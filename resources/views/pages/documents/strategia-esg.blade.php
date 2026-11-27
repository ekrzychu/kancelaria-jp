@php
  $contents = [
      'wprowadzenie' => 'Wprowadzenie',
      'dialog' => 'Dialog z interesariuszami',
      'cele' => 'Cele zrównoważonego rozwoju',
      'dzialania' => 'Nasze działania',
      'monitorowanie' => 'Monitorowanie strategii ESG',
  ];
@endphp
@extends('pages.document')

@section('document-title', 'Strategia zrównoważonego rozwoju (ESG)')

@section('document-content')
<section id="wprowadzenie" data-reveal>
  <h2>{{ $contents['wprowadzenie'] }}</h2>
  <p>ESG to część strategii biznesowej Jaroch-Konwent Pakos Kancelaria Radców Prawnych sp.k. oraz jeden z jej priorytetów. Strategia integruje zarządzanie ESG z działalnością i wyznacza długofalowe cele dotyczące środowiska, społeczeństwa i ładu korporacyjnego.</p>
</section>

<section id="dialog" data-reveal>
  <h2>{{ $contents['dialog'] }}</h2>
  <p>Budujemy wiarygodne relacje i zaufanie poprzez przejrzyste działanie zgodne z wartościami ESG. Przygotowanie strategii opierało się na dialogu oraz analizie kwestii zrównoważonego rozwoju ważnych dla klientów i partnerów wewnętrznych oraz zewnętrznych.</p>
</section>

<section id="cele" data-reveal>
  <h2>{{ $contents['cele'] }}</h2>
  <p>Strategia jest zgodna z Celami Zrównoważonego Rozwoju przyjętymi w Agendzie ONZ 2030. Koncentruje się na pięciu celach.</p>
  <ol class="document-goals">
    <li value="4"><strong>Dobra jakość edukacji</strong><p>Zapewnienie dostępu do wysokiej jakości edukacji i możliwości uczenia się przez całe życie.</p></li>
    <li value="5"><strong>Równość płci</strong><p>Równouprawnienie i wzmocnienie pozycji kobiet oraz dziewcząt.</p></li>
    <li value="8"><strong>Wzrost gospodarczy i godna praca</strong><p>Trwały wzrost gospodarczy, pełne i produktywne zatrudnienie oraz godne warunki pracy dla wszystkich.</p></li>
    <li value="10"><strong>Mniej nierówności</strong><p>Ograniczanie nierówności zarówno wewnątrz krajów, jak i między nimi.</p></li>
    <li value="16"><strong>Pokój, sprawiedliwość i silne instytucje</strong><p>Wspieranie pokojowych i otwartych społeczeństw, dostępu do wymiaru sprawiedliwości oraz skutecznych, odpowiedzialnych instytucji sprzyjających włączeniu.</p></li>
  </ol>
</section>

<section id="dzialania" data-reveal>
  <h2>{{ $contents['dzialania'] }}</h2>
  <h3>Środowisko (E)</h3>
  <ul>
    <li>Uwzględniamy klimat przy decyzjach biznesowych i wspieramy inicjatywy środowiskowe.</li>
    <li>Ograniczamy wpływ na przyrodę, dążąc do redukcji własnych emisji poprzez dekarbonizację działalności. Udostępniamy pracownikom wodę w szkle i segregujemy odpady.</li>
    <li>Przy cyfryzacji procesów uwzględniamy wpływ na środowisko.</li>
  </ul>
  <h3>Społeczność (S)</h3>
  <ul>
    <li>Tworzymy środowisko pracy atrakcyjne i wspierające różnorodność, równość i włączenie.</li>
    <li>Wspieramy zdrowie pracowników poprzez dofinansowanie kart MultiSport oraz opieki medycznej LuxMed.</li>
    <li>Wspieramy przedsiębiorczość poprzez usługi prawne i działania pro bono, przede wszystkim dla przedsiębiorców, w tym szkolenia, newslettery i edukację.</li>
    <li>Prowadzimy mediacje i pomagamy klientom lepiej rozumieć regulacje.</li>
    <li>Wspieramy lokalne inicjatywy charytatywne, edukacyjne, zdrowotne i środowiskowe.</li>
  </ul>
  <h3>Ład korporacyjny (G)</h3>
  <ul>
    <li>Pracujemy w oparciu o wartości, zasady i procedury etyczne, stosujemy regulacje i dobre praktyki rynkowe.</li>
    <li>Włączamy ESG do ładu korporacyjnego, strategii, struktury, projektowania usług, zarządzania ryzykiem i zakupów. Bierzemy pod uwagę ryzyka ESG w analizie działalności.</li>
    <li>Rozwijamy wiedzę pracowników i klientów o ESG.</li>
  </ul>
  <p>Wspierają nas polityki dotyczące ochrony środowiska, przeciwdziałania niepożądanym zjawiskom w pracy, równości, różnorodności i osób wykluczonych oraz bezpieczeństwa informacji. Wdrożyliśmy również <a href="/dokumenty/kodeks-etyczny/">kodeks etyczny</a> i procedurę przeciwdziałania praniu pieniędzy oraz finansowaniu terroryzmu.</p>
</section>

<section id="monitorowanie" data-reveal>
  <h2>{{ $contents['monitorowanie'] }}</h2>
  <p>Za monitorowanie realizacji strategii odpowiada wspólniczka Marta Jaroch-Konwent. Regularnie przedstawia zarządowi kancelarii postępy i zaleca potrzebne działania naprawcze.</p>
</section>
@endsection
