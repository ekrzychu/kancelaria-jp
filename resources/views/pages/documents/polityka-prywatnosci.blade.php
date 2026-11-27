@php
  $contents = [
      'administrator' => '1. Administrator danych',
      'kontakt' => '2. Kontakt i korespondencja',
      'odbiorcy' => '3. Odbiorcy danych i infrastruktura',
      'media-spolecznosciowe' => '4. Media społecznościowe',
      'pliki-cookies' => '5. Sesja i pliki cookies',
      'bezpieczenstwo' => '6. Bezpieczeństwo i logi',
      'statystyki' => '7. Statystyki odsłon',
      'prawa' => '8. Państwa prawa',
  ];
@endphp
@extends('pages.document')

@section('document-note')
<p class="document-note">Projekt do zatwierdzenia przez Kancelarię.</p>
@endsection

@section('document-content')
<section data-reveal>
  <h2 id="administrator">{{ $contents['administrator'] }}</h2>
  <p>Administratorem danych jest Jaroch-Konwent Pakos Kancelaria Radców Prawnych sp.k. (dawniej Jaroch Pakos Kancelaria Radców Prawnych s.c.), NIP 8971858043, REGON 380898156. Kontakt: pl. Tadeusza Kościuszki 7–8/1, 50-028 Wrocław, tel. <a href="tel:+48880383880">880 383 880</a>, <a href="mailto:kontakt@kancelaria-jp.pl">kontakt@kancelaria-jp.pl</a> lub <a href="/kontakt/#formularz">formularz kontaktowy</a>.</p>
  <p>RODO oznacza <a href="https://eur-lex.europa.eu/eli/reg/2016/679/oj?locale=pl">rozporządzenie (UE) 2016/679</a> o ochronie danych osobowych.</p>
</section>

<section data-reveal>
  <h2 id="kontakt">{{ $contents['kontakt'] }}</h2>
  <p>Obsługujemy zapytania otrzymane przez formularz, pocztę elektroniczną, telefon i profile Kancelarii w mediach społecznościowych. Dane wykorzystujemy, aby zidentyfikować nadawcę, udzielić odpowiedzi i zająć się przedstawioną sprawą. Dane potrzebne do obsługi zapytania są wymagane, abyśmy mogli odpowiedzieć.</p>
  <ul>
    <li>Obsługa komunikacji oraz marketing własnych usług w ramach dotychczas opisanych celów opierają się na prawnie uzasadnionym interesie Kancelarii (art. 6 ust. 1 lit. f RODO).</li>
    <li>Jeżeli kontakt dotyczy działań przed zawarciem umowy na Państwa żądanie albo wykonania umowy, podstawą przetwarzania niezbędnych danych jest art. 6 ust. 1 lit. b RODO.</li>
    <li>Dobrowolne dane dodatkowe, np. ułatwiające kontakt, przetwarzamy na podstawie zgody (art. 6 ust. 1 lit. a RODO), jeśli to zgoda jest podstawą ich podania.</li>
  </ul>
  <p>Formularz zapisuje dane kontaktowe i treść wiadomości w bazie MariaDB. Dostęp do korespondencji w panelu mają użytkownicy z odpowiednimi uprawnieniami. Po zapisaniu system próbuje przekazać wiadomość pocztą elektroniczną. W razie problemu z dostarczeniem wiadomość pozostaje w panelu i można ponowić wysyłkę.</p>
  <p>Dane przechowujemy przez czas obsługi sprawy, a w razie zawarcia umowy przez czas niezbędny do jej wykonania. W zależności od sprawy możemy je zachować do upływu terminu przedawnienia roszczeń na podstawie art. 6 ust. 1 lit. f RODO. Jeżeli ma to zastosowanie, uwzględniamy wycofanie zgody lub skuteczny sprzeciw. Zapisanych wiadomości aplikacja sama nie usuwa. Kancelaria stosuje właściwe dla sprawy okresy i procedury wobec danych w panelu, poczcie oraz kopiach zapasowych.</p>
  <p>Jeżeli składają Państwo aplikację na ofertę pracy, prosimy zapoznać się z informacjami dla kandydatów i skorzystać z kanału wskazanego w danej ofercie.</p>
</section>

<section data-reveal>
  <h2 id="odbiorcy">{{ $contents['odbiorcy'] }}</h2>
  <p>Dostęp do danych w zakresie potrzebnym do świadczenia usług mogą mieć podmioty wspierające Kancelarię, w tym dostawcy usług informatycznych i telekomunikacyjnych, hostingu oraz poczty. Przewidywana konfiguracja produkcyjna obejmuje infrastrukturę hostingową i wysyłkę SMTP w dhosting oraz odbiór poczty pod adresem kontakt@kancelaria-jp.pl w OVH. Szczegóły tej konfiguracji i informacje o dostawcach wymagają potwierdzenia przed przyjęciem dokumentu.</p>
</section>

<section data-reveal>
  <h2 id="media-spolecznosciowe">{{ $contents['media-spolecznosciowe'] }}</h2>
  <p>Gdy kontaktują się Państwo z Kancelarią przez jej profile, Kancelaria przetwarza dane potrzebne do obsługi interakcji, takie jak nazwa profilu, widoczne zdjęcie i treść wiadomości lub komentarza. Operatorzy serwisów przetwarzają dane również według własnych zasad. Linki do profili prowadzą poza stronę Kancelarii.</p>
  <ul>
    <li>Facebook – <a href="https://www.facebook.com/privacy/policy/">polityka prywatności</a> i <a href="https://www.facebook.com/legal/terms/page_controller_addendum">zasady statystyk stron</a>.</li>
    <li>Instagram – <a href="https://privacycenter.instagram.com/policy/">polityka prywatności</a>.</li>
    <li>LinkedIn – <a href="https://pl.linkedin.com/legal/privacy-policy">polityka prywatności</a>.</li>
    <li>Jeśli kontakt odbywa się przez profil Kancelarii w TikToku, stosuje się również <a href="https://www.tiktok.com/legal/page/eea/privacy-policy/pl">politykę prywatności TikToka</a>.</li>
  </ul>
  <p>Dane związane z obserwowaniem profilu mogą być widoczne dla Kancelarii przez okres obserwowania. Można przestać obserwować profil lub usunąć własne komentarze zgodnie z zasadami serwisu. Kancelaria obsługuje żądania w zakresie danych, do których ma dostęp. W sprawie danych pozostających u operatora można skontaktować się z nim bezpośrednio.</p>
</section>

<section data-reveal>
  <h2 id="pliki-cookies">{{ $contents['pliki-cookies'] }}</h2>
  <p>Formularz kontaktowy i panel korzystają z mechanizmu sesji Laravel. Niezbędny plik cookie pozwala powiązać żądanie z sesją. Laravel może też ustawić techniczny plik cookie XSRF-TOKEN na potrzeby ochrony przed wysłaniem żądania z obcej strony (CSRF). Mechanizmy te służą obsłudze i zabezpieczeniu formularza oraz logowania w panelu. Dane sesji w bazie mogą obejmować adres IP, informacje o przeglądarce i czas ostatniej aktywności.</p>
  <p>Można zarządzać plikami cookies w przeglądarce. Ich zablokowanie może uniemożliwić wysłanie formularza lub zalogowanie się do panelu.</p>
</section>

<section data-reveal>
  <h2 id="bezpieczenstwo">{{ $contents['bezpieczenstwo'] }}</h2>
  <p>Przy obsłudze połączenia serwer otrzymuje techniczne dane żądania, w tym adres IP. Formularz ogranicza liczbę prób przesłania wiadomości. Dostęp do panelu i do korespondencji podlega uwierzytelnieniu oraz kontroli uprawnień. Serwer i infrastruktura mogą zapisywać zdarzenia potrzebne do utrzymania usługi, diagnozowania błędów i ochrony przed nadużyciami. Zakres, dostęp i okres przechowywania logów wymagają potwierdzenia dla docelowej konfiguracji.</p>
</section>

<section data-reveal>
  <h2 id="statystyki">{{ $contents['statystyki'] }}</h2>
  <p>Po włączeniu własne statystyki serwisu zliczają kwalifikujące się odsłony publicznych stron HTML według dnia, strony i ogólnej kategorii źródła ruchu. Statystyki są domyślnie wyłączone. Zestawienia mogą także pokazywać łączną liczbę przyjętych formularzy na podstawie zapisów w bazie, bez udostępniania w statystykach danych nadawców ani treści wiadomości.</p>
  <p>Licznik odsłon może policzyć wejście tej samej osoby kilka razy. Z narzędzia nie można odtworzyć historii danej osoby ani ustalić liczby unikalnych odwiedzających. W zbiorze statystyk nie zapisujemy adresów IP, identyfikatorów cookies, identyfikatorów przeglądarek, pełnych adresów odsyłających ani parametrów zapytań. Okres przechowywania dziennych liczników ustala Kancelaria w konfiguracji. Liczniki starsze od ustawionego okresu usuwa zadanie techniczne.</p>
</section>

<section data-reveal>
  <h2 id="prawa">{{ $contents['prawa'] }}</h2>
  <p>W przypadkach określonych w RODO mogą Państwo żądać dostępu do danych, ich sprostowania, usunięcia, ograniczenia przetwarzania lub przeniesienia. Mogą Państwo zgłosić sprzeciw wobec przetwarzania opartego na prawnie uzasadnionym interesie. Zgodę można wycofać w dowolnym momencie. Pozostaje to bez wpływu na zgodność z prawem działań podjętych wcześniej na jej podstawie.</p>
  <p>W celu wykonania tych praw prosimy o kontakt wskazany w <a href="#administrator">sekcji o administratorze</a>. Mogą Państwo również wnieść skargę do <a href="https://uodo.gov.pl/">Prezesa Urzędu Ochrony Danych Osobowych</a>.</p>
</section>
@endsection
