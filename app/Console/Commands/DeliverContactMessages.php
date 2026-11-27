<?php

namespace App\Console\Commands;

use App\Models\ContactMessage;
use Illuminate\Console\Command;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

final class DeliverContactMessages extends Command
{
    protected $signature = 'contact:deliver';

    protected $description = 'Deliver due contact messages and retry failed deliveries';

    public function handle(): int
    {
        // A killed process may have sent the email before it could persist the result.
        $uncertain = ContactMessage::where('delivery_status', 'sending')
            ->where('last_attempt_at', '<=', now()->subMinutes(30))
            ->update(['delivery_status' => 'uncertain']);

        $due = ContactMessage::whereIn('delivery_status', ['pending', 'failed', 'uncertain'])
            ->where('next_attempt_at', '<=', now());
        $sent = $failed = 0;

        foreach ((clone $due)->orderBy('next_attempt_at')->orderBy('id')->limit(20)->pluck('id') as $id) {
            // Commit the claim before SMTP. Overlapping cron processes cannot claim the same row.
            $claimed = (clone $due)->whereKey($id)->update([
                'delivery_status' => 'sending',
                'delivery_attempts' => DB::raw('delivery_attempts + 1'),
                'last_attempt_at' => now(),
                'next_attempt_at' => null,
            ]);
            if (! $claimed) {
                continue;
            }

            $record = ContactMessage::findOrFail($id);
            $attempt = ContactMessage::whereKey($id)->where('delivery_status', 'sending')
                ->where('delivery_attempts', $record->delivery_attempts);
            try {
                $this->send($record);
            } catch (Throwable) {
                // Transport errors can contain SMTP credentials, addresses or message text.
                $attempt->update([
                    'delivery_status' => 'failed',
                    'next_attempt_at' => $record->delivery_attempts < 3 ? now()->addMinutes(5 * $record->delivery_attempts) : null,
                ]);
                $failed++;

                continue;
            }

            $sent += $attempt->update(['delivery_status' => 'sent', 'sent_at' => now()]);
        }

        $this->info("Contact delivery — sent: {$sent}, failed: {$failed}, newly uncertain: {$uncertain}.");

        return self::SUCCESS;
    }

    private function send(ContactMessage $record): void
    {
        $mailer = config('mail.default');
        if (app()->environment('local', 'testing')) {
            $mailer = 'array';
        }
        if (! in_array($mailer, ['array', 'smtp'], true)
            || ($mailer === 'smtp' && (! config('mail.mailers.smtp.host')
                || ! config('mail.mailers.smtp.username') || ! config('mail.mailers.smtp.password')
                || config('mail.from.address') === 'contact@example.test'))) {
            throw new RuntimeException('Contact mail is not configured.');
        }

        $body = "Imię i nazwisko: {$record->name}\nAdres e-mail: {$record->email}\n"
            .'Telefon: '.($record->phone ?? '—')."\n\nTreść wiadomości:\n{$record->message}";

        $sent = Mail::mailer($mailer)->raw($body, function (Message $message) use ($record): void {
            $message->to(config('mail.contact.to'))->replyTo($record->email)->subject('Nowa wiadomość z formularza kontaktowego');
            $message->getSymfonyMessage()->getHeaders()
                ->addIdHeader('Message-ID', $record->id.'@'.config('hosts.public'));
        });
        if ($sent === null) {
            throw new RuntimeException('Contact mail was not sent.');
        }
    }
}
