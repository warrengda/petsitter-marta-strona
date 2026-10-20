<?php
// Formularz kontaktowy petsitter.marta-olszewska.pl
// Adres odbiorcy: ten sam co w panelu (src/data/ustawienia.yaml).
const ODBIORCA = 'petsitter@marta-olszewska.pl';
const NADAWCA = 'petsitter@marta-olszewska.pl';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /kontakt/', true, 303);
    exit;
}

// Pułapka na boty: pole ukryte musi zostać puste
if (!empty($_POST['strona'])) {
    header('Location: /kontakt/dziekuje/', true, 303);
    exit;
}

$czysc = fn($v) => trim(str_replace(["\r", "\n"], ' ', (string)($v ?? '')));
$imie = mb_substr($czysc($_POST['imie'] ?? ''), 0, 100);
$kontakt = mb_substr($czysc($_POST['kontakt'] ?? ''), 0, 150);
$zwierzak = mb_substr($czysc($_POST['zwierzak'] ?? ''), 0, 50);
$od = mb_substr($czysc($_POST['od'] ?? ''), 0, 10);
$do = mb_substr($czysc($_POST['do'] ?? ''), 0, 10);
$wiadomosc = mb_substr(trim((string)($_POST['wiadomosc'] ?? '')), 0, 5000);

if ($imie === '' || $kontakt === '' || $wiadomosc === '') {
    header('Location: /kontakt/', true, 303);
    exit;
}

$temat = '=?UTF-8?B?' . base64_encode("Zapytanie ze strony: $imie") . '?=';
$tresc = "Imię: $imie\nKontakt: $kontakt\nZwierzak: $zwierzak\nTermin: $od – $do\n\n$wiadomosc\n";
$naglowki = [
    'From: Strona petsitter <' . NADAWCA . '>',
    'Content-Type: text/plain; charset=UTF-8',
];
if (filter_var($kontakt, FILTER_VALIDATE_EMAIL)) {
    $naglowki[] = 'Reply-To: ' . $kontakt;
}

$ok = mail(ODBIORCA, $temat, $tresc, implode("\r\n", $naglowki));
// Rodzaj usługi trafia do GA4 jako service_type (bez danych osobowych)
$typ = ['Mały pies' => 'maly-pies', 'Średni pies' => 'sredni-pies', 'Dwa psy' => 'dwa-psy', 'Kot' => 'kot', 'Spacery z psem' => 'spacery'][$zwierzak] ?? 'inne';
header('Location: /kontakt/dziekuje/?typ=' . $typ . ($ok ? '' : '&blad=1'), true, 303);
