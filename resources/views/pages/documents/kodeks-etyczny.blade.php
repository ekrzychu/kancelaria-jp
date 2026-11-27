@php
  $contents = [
      'wprowadzenie' => 'Wprowadzenie',
      'misja' => 'Misja i wartości',
      'zrownowazony-rozwoj' => 'Realizacja celów zrównoważonego rozwoju',
      'personel' => 'Zasady dotyczące członków personelu Kancelarii',
      'klienci' => 'Relacje z klientami',
      'dostawcy' => 'Relacje z dostawcami',
      'zgloszenia' => 'Zgłaszanie naruszeń',
  ];
@endphp
@extends('pages.document')

@section('document-note')
<p>Obowiązuje od <time datetime="2024-01-01">1 stycznia 2024 r.</time></p>
@endsection

@section('document-content')
<section id="wprowadzenie" data-reveal>
  <h2>{{ $contents['wprowadzenie'] }}</h2>
  <p>Kodeks wyznacza standardy etyki zawodowej w Jaroch-Konwent Pakos Kancelaria Radców Prawnych sp.k. Stosuje się go w relacjach z personelem, klientami i innymi partnerami biznesowymi. Służy realizacji <a href="/dokumenty/strategia-esg/">Strategii ESG</a> oraz celów zrównoważonego rozwoju.</p>
</section>

<section id="misja" data-reveal>
  <h2>{{ $contents['misja'] }}</h2>
  <p>Kancelaria buduje kulturę etyczną, by wywiązywać się ze swoich zobowiązań i wspierać zrównoważony rozwój. Niezależnie od roli personel reprezentuje jej wartości poprzez zaangażowanie, współpracę, profesjonalizm oraz szacunek dla ludzi i środowiska.</p>
  <p>Rozwój i zwiększanie wartości świadczonych usług mają służyć satysfakcji klientów, przy poszanowaniu praw pracowników, bezpieczeństwa pracy i uczciwej konkurencji. Kancelaria stosuje obowiązujące prawo, właściwe zalecenia nadzorcze i zawodowe kodeksy etyki radców prawnych oraz adwokatów.</p>
  <p>Każdemu pracownikowi przysługuje traktowanie z godnością i szacunkiem oraz równość przy zatrudnianiu, rozwiązywaniu stosunku pracy, ustalaniu jego warunków, awansach i dostępie do szkoleń zawodowych. Dotyczy to w szczególności płci, niepełnosprawności, narodowości, religii lub jej braku, poglądów politycznych, członkostwa w związkach zawodowych, orientacji seksualnej, stanu cywilnego i podstawy zatrudnienia. Polityka przeciwdziałania niepożądanym zjawiskom w miejscu pracy obejmuje także inne przyczyny dyskryminacji.</p>
  <p>Strategia ESG oraz polityka środowiskowa służą ograniczaniu negatywnego wpływu działalności kancelarii na zasoby naturalne i środowisko.</p>
</section>

<section id="zrownowazony-rozwoj" data-reveal>
  <h2>{{ $contents['zrownowazony-rozwoj'] }}</h2>
  <p>Kancelaria wspiera Agendę ONZ 2030. W strategii przyjęła w szczególności cele 4, 5, 8, 10 i 16, dotyczące edukacji, równości płci, godnej pracy, ograniczenia nierówności oraz pokoju, sprawiedliwości i odpowiedzialnych instytucji.</p>
  <h3>Środowisko (E)</h3>
  <ul>
    <li>Uwzględniamy klimat w decyzjach biznesowych i wspieramy inicjatywy środowiskowe.</li>
    <li>Dążymy do redukcji własnych emisji gazów cieplarnianych poprzez dekarbonizację działalności. Udostępniamy pracownikom wodę w szkle i segregujemy odpady.</li>
    <li>Przenosząc procesy do formy cyfrowej, bierzemy pod uwagę ich wpływ na środowisko.</li>
  </ul>
  <h3>Społeczność (S)</h3>
  <ul>
    <li>Dbamy o równość, różnorodność, włączenie i zdrowie pracowników.</li>
    <li>Wspieramy przedsiębiorczość poprzez pomoc prawną i działania pro bono, w tym szkolenia, newslettery i inne działania edukacyjne.</li>
    <li>Prowadzimy mediacje oraz pomagamy klientom lepiej rozumieć regulacje.</li>
    <li>Wspieramy lokalne inicjatywy charytatywne, edukacyjne, zdrowotne i środowiskowe.</li>
  </ul>
  <h3>Ład korporacyjny (G)</h3>
  <ul>
    <li>Stosujemy zasady etyczne, procedury, regulacje i dobre praktyki. Rozwijamy ład korporacyjny i wiedzę pracowników oraz klientów o ESG.</li>
    <li>Uwzględniamy ESG w strategii, strukturze, projektowaniu usług, zarządzaniu ryzykiem, zakupach i analizach ryzyka działalności.</li>
  </ul>
  <p>Wdrożone rozwiązania obejmują polityki ochrony środowiska, przeciwdziałania niepożądanym zjawiskom w pracy, wspierania równości, różnorodności i osób wykluczonych oraz bezpieczeństwa informacji. Kancelaria przyjęła również niniejszy kodeks i procedurę przeciwdziałania praniu pieniędzy i finansowaniu terroryzmu.</p>
</section>

<section id="personel" data-reveal>
  <h2>{{ $contents['personel'] }}</h2>
  <p>Od wszystkich zatrudnionych, bez względu na stanowisko, charakter pracy czy formę zatrudnienia, wymagamy przestrzegania procedur i najwyższych standardów zawodowych, w tym niniejszego kodeksu oraz właściwych kodeksów zawodowych. Naruszenie kodeksu może stanowić naruszenie obowiązków pracowniczych i skutkować wypowiedzeniem umowy o pracę lub innej umowy współpracy.</p>
  <p>Każdy zatrudniony odpowiada za właściwe korzystanie z zasobów potrzebnych do ciągłości działalności oraz za ochronę mienia kancelarii i przekazanego jej mienia osób trzecich. Polityka bezpieczeństwa informacji wymaga ochrony poufności, integralności i dostępności informacji z uwzględnieniem prawa i umów.</p>
  <p>Kancelaria zapewnia sprawiedliwe, bezpieczne i produktywne środowisko pracy oraz docenia wkład każdej osoby. Personel ma traktować współpracowników, klientów, dostawców i gości z szacunkiem i profesjonalizmem. Kancelaria przeciwdziała mobbingowi i stosuje polityki równości, różnorodności i przeciwdziałania wykluczeniu.</p>
  <p>Personel unika działań i powiązań sprzecznych także potencjalnie z interesem kancelarii, w tym działań konkurencyjnych. Personelowi nie wolno podejmować decyzji lub działań, które mogłyby spowodować konflikt z interesem kancelarii. Kancelaria przeciwdziała sprzeczności interesów kancelarii lub jej personelu z interesem klienta, a także interesu zatrudnionego z interesem kancelarii. O konflikcie, ryzyku jego wystąpienia lub wątpliwościach, również przy dodatkowych funkcjach lub zajęciach, należy poinformować bezpośredniego przełożonego.</p>
</section>

<section id="klienci" data-reveal>
  <h2>{{ $contents['klienci'] }}</h2>
  <p>Personel dąży do najwyższej jakości usług i obsługi, chroni interesy klientów, stosuje prawo, zawarte umowy oraz kodeksy etyki radców prawnych i adwokatów i unika konfliktów interesów przy świadczeniu pomocy prawnej. Kancelaria chroni poufne informacje i tajemnicę zawodową oraz komunikuje się w sposób otwarty, prosty i skuteczny.</p>
  <p>Kancelaria przestrzega przepisów antymonopolowych i prokonkurencyjnych. Stosuje kontrole i wewnętrzną procedurę przeciwdziałania praniu pieniędzy i finansowaniu terroryzmu zgodnie z prawem.</p>
  <p>W relacjach wewnętrznych i zewnętrznych kierujemy się prawością, uczciwością i sprawiedliwością. Zachowujemy neutralność polityczną i nie finansujemy partii. Obowiązuje zerowa tolerancja wobec korupcji, w tym bezpośredniego lub pośredniego oferowania, obiecywania, zatwierdzania, przekazywania, żądania lub przyjmowania nienależnych korzyści osobistych lub majątkowych w zamian za działanie lub zaniechanie związane z pracą na rzecz kancelarii.</p>
</section>

<section id="dostawcy" data-reveal>
  <h2>{{ $contents['dostawcy'] }}</h2>
  <p>Kancelaria prowadzi odpowiedzialne zakupy. Wybierając dostawców, bierze pod uwagę doświadczenie, umiejętności, jakość, terminy i warunki dostaw, możliwości finansowe oraz kryteria ESG. Od dostawców i kontrahentów oczekuje przestrzegania kodeksu, standardów etycznych i warunków pracy oraz spełniania kryteriów społecznych i środowiskowych.</p>
  <p>Utrzymujemy kontakt i relacje oparte na zaufaniu, uczciwości i wspólnej odpowiedzialności za BHP, prawa człowieka, etykę biznesu, pracowników i środowisko. Przeciwdziałamy opóźnieniom w płatnościach na rzecz partnerów i dostawców.</p>
</section>

<section id="zgloszenia" data-reveal>
  <h2>{{ $contents['zgloszenia'] }}</h2>
  <p>Pracownik lub współpracownik, który ma informacje lub uzasadnione podejrzenie naruszenia prawa, procedur bądź standardów etycznych, ma obowiązek niezwłocznie zgłosić sprawę pisemnie na adres siedziby albo na <a href="mailto:zgloszenia@kancelaria-jp.pl">zgloszenia@kancelaria-jp.pl</a>. Kancelaria zapewnia anonimowy kanał, poufność wszystkich zgłoszeń i brak działań odwetowych wobec zgłaszających potencjalne naruszenia.</p>
  <p>Przedstawiciel klienta, dostawcy lub innego partnera biznesowego może zgłosić naruszenie prawa lub kodeksu listownie na adres siedziby albo na <a href="mailto:kontakt@kancelaria-jp.pl">kontakt@kancelaria-jp.pl</a>.</p>
  <h3>Przykładowe naruszenia</h3>
  <ul>
    <li>Pośrednie lub bezpośrednie naruszenie prawa, kodeksu, zasad etyki zawodowej lub wewnętrznych regulacji.</li>
    <li>Podanie informacji nieprawdziwych lub mylących albo celowe zatajenie informacji istotnych, również podczas wyjaśniania sprawy.</li>
    <li>Niewłaściwy nadzór nad zatrudnionymi.</li>
    <li>Brak reakcji na znane lub podejrzewane naruszenie oraz celowe ignorowanie dostępnych w zakresie obowiązków informacji, które pozwoliłyby je rozpoznać.</li>
    <li>Odwet na osobie zgłaszającej podejrzenie bezprawnego lub nieetycznego zachowania.</li>
    <li>Celowe zgłoszenie fałszywego podejrzenia takiego zachowania.</li>
  </ul>
</section>
@endsection
