<?php

declare(strict_types=1);

use App\Actions\ManageAccount;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;

if (getenv('APP_ENV') !== 'testing' || getenv('DB_USERNAME') !== 'kjp_test'
    || getenv('DB_HOST') !== '127.0.0.1' || getenv('DB_PORT') === '3306'
    || ! preg_match('/\Akancelaria_jp_test_[a-f0-9]{16}\z/', getenv('DB_DATABASE') ?: '')) {
    exit(1);
}

[, $base, $operation] = $argv;
require $base.'/vendor/autoload.php';
$app = require $base.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if ($operation === 'provision') {
    $data = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
    app(ManageAccount::class)->provisionFirstAdministrator($data['name'], $data['email'], $data['password']);
    echo 'Provisioned'.PHP_EOL;
} elseif ($operation === 'recovery-origin') {
    /** @var User $user */
    $user = User::firstOrFail();
    $request = \Illuminate\Http\Request::create(config('hosts.admin_url'));
    app('url')->setRequest($request);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $status = Password::sendResetLink(
        ['email' => $user->email, 'is_active' => true],
        function (User $account, #[SensitiveParameter] string $token): void {
            $url = Filament::getResetPasswordUrl($token, $account);
            if (! app('url')->hasValidSignature(\Illuminate\Http\Request::create($url))) {
                exit(3);
            }
            $parts = parse_url($url);
            echo $parts['scheme'].'://'.$parts['host'].':'.$parts['port'].$parts['path'];
        },
    );
    if ($status !== Password::RESET_LINK_SENT) {
        exit(3);
    }
} elseif ($operation === 'login-attempts') {
    echo RateLimiter::attempts('livewire-rate-limiter:'.sha1(\App\Filament\Pages\Login::class.'|authenticate|127.0.0.1'));
} elseif ($operation === 'contact-delivery') {
    $attempts = [];
    Event::listen(MessageSending::class, function (MessageSending $event) use (&$attempts, $argv): void {
        $mail = $event->message;
        $attempts[] = [
            'to' => array_map(fn ($address) => $address->getAddress(), $mail->getTo()),
            'from' => array_map(fn ($address) => $address->getAddress(), $mail->getFrom()),
            'reply_to' => array_map(fn ($address) => $address->getAddress(), $mail->getReplyTo()),
            'cc' => $mail->getCc(),
            'bcc' => $mail->getBcc(),
            'body' => $mail->getTextBody(),
            'html' => $mail->getHtmlBody(),
            'message_id' => $mail->getHeaders()->get('Message-ID')->getId(),
        ];
        if (($argv[3] ?? '') === 'fail') {
            throw new RuntimeException('Synthetic confidential transport failure');
        }
    });
    $exit = Artisan::call('contact:deliver');
    echo json_encode([
        'exit' => $exit,
        'output' => Artisan::output(),
        'attempts' => $attempts,
        'delivered' => Mail::mailer('array')->getSymfonyTransport()->messages()->count(),
    ], JSON_THROW_ON_ERROR);
} elseif ($operation === 'deactivate') {
    [, , , $operator, $target, $start] = $argv;
    Auth::setUser(User::findOrFail($operator));
    $account = User::findOrFail($target);
    while (microtime(true) < (float) $start) {
        usleep(1000);
    }
    try {
        app(ManageAccount::class)->setActive($account, false);
        echo 'Changed';
    } catch (\Illuminate\Auth\Access\AuthorizationException | \Illuminate\Validation\ValidationException) {
        echo 'Denied';
    }
} else {
    exit(2);
}
