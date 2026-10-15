<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use UnexpectedValueException;

class JapaneseCallsignLookup
{
    private const MIC_URL = 'https://www.tele.soumu.go.jp/musen/list';

    private const PREFECTURES = [
        1 => ['北海道', 'Hokkaido'],
        2 => ['青森県', 'Aomori'],
        3 => ['岩手県', 'Iwate'],
        4 => ['秋田県', 'Akita'],
        5 => ['山形県', 'Yamagata'],
        6 => ['宮城県', 'Miyagi'],
        7 => ['福島県', 'Fukushima'],
        8 => ['新潟県', 'Niigata'],
        9 => ['長野県', 'Nagano'],
        10 => ['東京都', 'Tokyo'],
        11 => ['神奈川県', 'Kanagawa'],
        12 => ['千葉県', 'Chiba'],
        13 => ['埼玉県', 'Saitama'],
        14 => ['茨城県', 'Ibaraki'],
        15 => ['栃木県', 'Tochigi'],
        16 => ['群馬県', 'Gunma'],
        17 => ['山梨県', 'Yamanashi'],
        18 => ['静岡県', 'Shizuoka'],
        19 => ['岐阜県', 'Gifu'],
        20 => ['愛知県', 'Aichi'],
        21 => ['三重県', 'Mie'],
        22 => ['京都府', 'Kyoto'],
        23 => ['滋賀県', 'Shiga'],
        24 => ['奈良県', 'Nara'],
        25 => ['大阪府', 'Osaka'],
        26 => ['和歌山県', 'Wakayama'],
        27 => ['兵庫県', 'Hyogo'],
        28 => ['富山県', 'Toyama'],
        29 => ['福井県', 'Fukui'],
        30 => ['石川県', 'Ishikawa'],
        31 => ['岡山県', 'Okayama'],
        32 => ['島根県', 'Shimane'],
        33 => ['山口県', 'Yamaguchi'],
        34 => ['鳥取県', 'Tottori'],
        35 => ['広島県', 'Hiroshima'],
        36 => ['香川県', 'Kagawa'],
        37 => ['徳島県', 'Tokushima'],
        38 => ['愛媛県', 'Ehime'],
        39 => ['高知県', 'Kochi'],
        40 => ['福岡県', 'Fukuoka'],
        41 => ['佐賀県', 'Saga'],
        42 => ['長崎県', 'Nagasaki'],
        43 => ['熊本県', 'Kumamoto'],
        44 => ['大分県', 'Oita'],
        45 => ['宮崎県', 'Miyazaki'],
        46 => ['鹿児島県', 'Kagoshima'],
        47 => ['沖縄県', 'Okinawa'],
    ];

    public function find(string $callsign): array
    {
        $payload = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
                . 'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0 Safari/537.36',
            'Accept' => 'application/json,text/plain,*/*',
            'Referer' => 'https://www.tele.soumu.go.jp/j/musen/webapi/',
        ])->timeout(12)->get(self::MIC_URL, [
            'ST' => '1',
            'OF' => '2',
            'DA' => '1',
            'OW' => 'AT',
            'DC' => '2',
            'SC' => '1',
            'MA' => $callsign,
        ])->throw()->json();

        if (!\is_array($payload)) {
            throw new UnexpectedValueException('MIC returned an invalid JSON response.');
        }

        $prefectures = [];
        foreach ($payload['musen'] ?? [] as $station) {
            $details = $station['detailInfo'] ?? null;
            if (!\is_array($details)) {
                continue;
            }

            $foundCallsign = strtoupper(trim((string) ($details['identificationSignals'] ?? '')));
            if ($foundCallsign !== $callsign) {
                continue;
            }

            $location = (string) ($details['radioEuipmentLocation'] ?? '');
            foreach (self::PREFECTURES as $number => [$japaneseName, $englishName]) {
                if (str_contains($location, $japaneseName)) {
                    $prefectures[$number] = [
                        'number' => $number,
                        'name' => $englishName,
                    ];
                    break;
                }
            }
        }

        ksort($prefectures);

        return array_values($prefectures);
    }
}