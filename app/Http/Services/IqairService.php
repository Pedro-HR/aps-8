<?php

declare(strict_types=1);

namespace App\Http\Services;

class IqairService
{
    private string $url;
    private string $endpoint;
    private string $accessToken;
    private array $data;
    private array $responseData = [];

    private const states = [
        ['state' => 'Acre', 'city' => 'Rio Branco'],
        ['state' => 'Alagoas', 'city' => 'Maceio'],
        ['state' => 'Amapa', 'city' => 'Macapa'],
        ['state' => 'Amazonas', 'city' => 'Manaus'],
        ['state' => 'Bahia', 'city' => 'Salvador'],
        ['state' => 'Ceara', 'city' => 'Fortaleza'],
        ['state' => 'Distrito Federal', 'city' => 'Brasilia'],
        ['state' => 'Espirito Santo', 'city' => 'Vitoria'],
        ['state' => 'Goias', 'city' => 'Goiania'],
        ['state' => 'Maranhao', 'city' => 'Sao Luis'],
        ['state' => 'Mato Grosso', 'city' => 'Cuiaba'],
        ['state' => 'Mato Grosso do Sul', 'city' => 'Campo Grande'],
        ['state' => 'Minas Gerais', 'city' => 'Belo Horizonte'],
        ['state' => 'Para', 'city' => 'Belem'],
        ['state' => 'Paraiba', 'city' => 'Joao Pessoa'],
        ['state' => 'Parana', 'city' => 'Curitiba'],
        ['state' => 'Pernambuco', 'city' => 'Recife'],
        ['state' => 'Piaui', 'city' => 'Teresina'],
        ['state' => 'Rio de Janeiro', 'city' => 'Rio de Janeiro'],
        ['state' => 'Rio Grande do Norte', 'city' => 'Natal'],
        ['state' => 'Rio Grande do Sul', 'city' => 'Porto Alegre'],
        ['state' => 'Rondonia', 'city' => 'Porto Velho'],
        ['state' => 'Roraima', 'city' => 'Boa Vista'],
        ['state' => 'Santa Catarina', 'city' => 'Florianopolis'],
        ['state' => 'São Paulo', 'city' => 'Sao Paulo'],
        ['state' => 'Sergipe', 'city' => 'Aracaju'],
        ['state' => 'Tocantins', 'city' => 'Palmas'],
    ];

    public function __construct()
    {
        $this->accessToken = env('IQAIR_TOKEN');
    }


    public function sendCurl(): array
    {
        $this->prepareUrl();

        $curl = curl_init($this->url);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ]);

        $result = curl_exec($curl);
        curl_close($curl);

        return json_decode($result, true);
    }

    private function prepareUrl()
    {
        $this->data['key'] = $this->accessToken;
        $this->url = $this->endpoint . http_build_query($this->data);
    }

    public function city(): array
    {
        foreach (self::states as $state) {
            $this->endpoint = 'https://api.airvisual.com/v2/city?';
            $this->data = [
                'city' => $state['city'],
                'state' => $state['state'],
                'country' => 'Brazil',
            ];

            $response = $this->sendCurl();
            if ($response['status'] != "fail" && !empty($response)){
                $this->prepareCityData($response);
            }
        }

        return $this->citysWithRanks($this->responseData);
    }

    private function prepareCityData(array $response): void
    {
        $data = [
            'state' => $response['data']['state'],
            'city' => $response['data']['city'],
            'rank' => 0,
            'hu' => $response['data']['current']['weather']['hu'],
            'tp' => $response['data']['current']['weather']['tp'],
            'ws' => $response['data']['current']['weather']['ws'],
            'ic' => $response['data']['current']['weather']['ic'],
            'aqius' => $response['data']['current']['pollution']['aqius'],
        ];

        array_push($this->responseData, $data);
    }

    private function citysWithRanks(array $responseData): array
    {
        $order = 1;

        $ranksOrder= collect($responseData)->sortByDesc('aqius')->toArray();
        foreach ($ranksOrder as &$rankOrder) {
            $rankOrder['rank'] = $order;
            $order++;
        }

        return $ranksOrder;
    }

}
