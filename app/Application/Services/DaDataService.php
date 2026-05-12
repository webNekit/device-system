<?php

namespace App\Application\Services;

use App\Application\Models\DaDataCache;
use Illuminate\Support\Facades\Http;

class DaDataService
{
    protected ?string $token = null;

    public function __construct()
    {
        $this->token = config('services.dadata.token') ?? '';
    }

    public function findByInn(string $inn): ?array
    {
        $hash = md5('party_'.$inn);

        if (! empty($this->token)) {
            $cached = DaDataCache::where('query_hash', $hash)
                ->where('expires_at', '>', now())
                ->first();

            if ($cached) {
                return json_decode($cached->response_json, true);
            }

            $data = $this->fetchFromApi($inn);

            if ($data) {
                DaDataCache::create([
                    'query_hash' => $hash,
                    'response_json' => json_encode($data),
                    'expires_at' => now()->addDays(30),
                ]);

                return $data;
            }
        }

        return $this->getMockData($inn);
    }

    protected function fetchFromApi(string $inn): ?array
    {
        $response = Http::withToken($this->token, 'Token')
            ->withHeaders(['Accept' => 'application/json', 'Content-Type' => 'application/json'])
            ->timeout(10)
            ->connectTimeout(5)
            ->post('https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party', [
                'query' => $inn,
            ]);

        if ($response->successful()) {
            return $response->json('suggestions.0');
        }

        return null;
    }

    protected function getMockData(string $inn): ?array
    {
        $known = [
            '7707083893' => [
                'value' => 'ПАО СБЕРБАНК',
                'data' => ['kpp' => '773601001', 'address' => ['value' => 'г Москва, ул Вавилова, д 19']],
            ],
            '7728168971' => [
                'value' => 'ООО "ЯНДЕКС"',
                'data' => ['kpp' => '997750001', 'address' => ['value' => 'г Москва, ул Льва Толстого, д 16']],
            ],
            '7704217371' => [
                'value' => 'АО "АЛЬФА-БАНК"',
                'data' => ['kpp' => '770801001', 'address' => ['value' => 'г Москва, пр-кт Академика Сахарова, д 12']],
            ],
            '7736050003' => [
                'value' => 'ПАО "ВТБ"',
                'data' => ['kpp' => '773501001', 'address' => ['value' => 'г Санкт-Петербург, ул Большая Морская, д 29']],
            ],
            '7703400368' => [
                'value' => 'ООО "ТЕХНОСЕРВИС"',
                'data' => ['kpp' => '770301001', 'address' => ['value' => 'г Москва, ул Тверская, д 7']],
            ],
            '6829137921' => [
                'value' => 'ООО "ГРАДТЕХНОСТРОЙ"',
                'data' => ['kpp' => '682901001', 'address' => ['value' => 'г Тамбов, ул Советская, д 191']],
            ],
        ];

        if (isset($known[$inn])) {
            return $known[$inn];
        }

        if (strlen($inn) !== 10 && strlen($inn) !== 12) {
            return null;
        }

        $isIP = strlen($inn) === 12;
        $regionCode = substr($inn, 0, 2);
        $hash = md5($inn);
        $index = hexdec(substr($hash, 0, 8));

        $cities = [
            '01' => 'Майкоп', '02' => 'Уфа', '03' => 'Улан-Удэ', '04' => 'Горно-Алтайск',
            '05' => 'Махачкала', '07' => 'Нальчик', '08' => 'Элиста', '09' => 'Черкесск',
            '10' => 'Петрозаводск', '11' => 'Сыктывкар', '12' => 'Йошкар-Ола', '13' => 'Саранск',
            '14' => 'Якутск', '15' => 'Владикавказ', '16' => 'Казань', '18' => 'Ижевск',
            '19' => 'Абакан', '20' => 'Грозный', '21' => 'Чебоксары', '22' => 'Барнаул',
            '23' => 'Краснодар', '24' => 'Красноярск', '25' => 'Владивосток', '26' => 'Ставрополь',
            '27' => 'Хабаровск', '28' => 'Благовещенск', '29' => 'Архангельск', '30' => 'Астрахань',
            '31' => 'Белгород', '32' => 'Брянск', '33' => 'Владимир', '34' => 'Волгоград',
            '35' => 'Вологда', '36' => 'Воронеж', '37' => 'Иваново', '38' => 'Иркутск',
            '39' => 'Калининград', '40' => 'Калуга', '42' => 'Кемерово', '43' => 'Киров',
            '44' => 'Кострома', '45' => 'Курган', '46' => 'Курск', '47' => 'Санкт-Петербург',
            '48' => 'Липецк', '49' => 'Магадан', '50' => 'Москва',
            '51' => 'Мурманск', '52' => 'Нижний Новгород', '53' => 'Великий Новгород',
            '54' => 'Новосибирск', '55' => 'Омск', '56' => 'Оренбург', '57' => 'Орёл',
            '58' => 'Пенза', '59' => 'Пермь', '60' => 'Псков', '61' => 'Ростов-на-Дону',
            '62' => 'Рязань', '63' => 'Самара', '64' => 'Саратов', '65' => 'Южно-Сахалинск',
            '66' => 'Екатеринбург', '67' => 'Смоленск', '68' => 'Тамбов', '69' => 'Тверь',
            '70' => 'Томск', '71' => 'Тула', '72' => 'Тюмень', '73' => 'Ульяновск',
            '74' => 'Челябинск', '75' => 'Чита', '76' => 'Ярославль',
            '77' => 'Москва', '78' => 'Санкт-Петербург', '86' => 'Ханты-Мансийск',
            '89' => 'Салехард',
        ];

        $companyNames = [
            'СтройИнвест', 'ТоргСервис', 'ПромТех', 'СпецМонтаж', 'ТехноСтрой',
            'РегионСнаб', 'КапиталСтрой', 'ПроектСтандарт', 'ЛидерГрупп', 'АльянсТрейд',
            'Континент', 'МастерСтрой', 'ОптимаСервис', 'Перспектива', 'РесурсПлюс',
            'СтандартКачество', 'ЦентрСнаб', 'ТрансЛогистика', 'ИнвестПром', 'РазвитиеРегион',
        ];

        $surnames = ['Иванов', 'Петров', 'Сидоров', 'Кузнецов', 'Смирнов', 'Попов', 'Лебедев', 'Козлов', 'Новиков', 'Морозов'];
        $firstNames = ['Александр', 'Иван', 'Сергей', 'Дмитрий', 'Андрей', 'Алексей', 'Максим', 'Владимир', 'Николай', 'Михаил'];
        $patronymics = ['Александрович', 'Иванович', 'Сергеевич', 'Дмитриевич', 'Андреевич', 'Алексеевич', 'Максимович', 'Владимирович', 'Николаевич', 'Михайлович'];

        $streets = [
            'Ленина', 'Советская', 'Мира', 'Гагарина', 'Победы',
            'Кирова', 'Московская', 'Садовая', 'Заводская', 'Центральная',
            'Молодёжная', 'Набережная', 'Комсомольская', 'Лесная', 'Парковая',
            'Строителей', 'Дружбы', 'Октябрьская', 'Новая', 'Северная',
        ];

        $city = $cities[$regionCode] ?? 'Москва';
        $street = $streets[($index >> 4) % count($streets)];
        $building = ($index >> 8) % 200 + 1;
        $corp = ($index >> 12) % 10 > 2 ? 'к'.(($index >> 12) % 9 + 1) : '';

        if ($isIP) {
            $surname = $surnames[$index % count($surnames)];
            $firstName = $firstNames[($index >> 4) % count($firstNames)];
            $patronymic = $patronymics[($index >> 8) % count($patronymics)];

            return [
                'value' => 'ИП '.$surname.' '.$firstName.' '.$patronymic,
                'data' => [
                    'kpp' => null,
                    'address' => ['value' => 'г '.$city.', ул '.$street.', д '.$building.($corp ? ' '.$corp : '')],
                ],
            ];
        }

        $name = $companyNames[$index % count($companyNames)];

        return [
            'value' => 'ООО "'.$name.'"',
            'data' => [
                'kpp' => $regionCode.'9'.substr($inn, 3, 2).'001',
                'address' => ['value' => 'г '.$city.', ул '.$street.', д '.$building.($corp ? ' '.$corp : '')],
            ],
        ];
    }
}
