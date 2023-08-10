<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class GareController extends Controller
{
    //Appel de l'Api fourni par la SNCF

    public function appelGare()
    {
        $client = new Client([
            'verify' => storage_path('cacert.pem'),
        ]);

        $response = $client->get('https://ressources.data.sncf.com/api/records/1.0/search/?dataset=liste-des-gares&q=&rows=49&facet=libelle&facet=voyageurs&facet=code_ligne&refine.departemen=ALPES-MARITIMES');
        // Décodage du Json en objet PHP
        $jsonData = json_decode($response->getBody());

        $gares = [];

        foreach ($jsonData->records as $record) {
            $gare = $record->fields->libelle;
            $codeUic = $record->fields->code_uic;
            $codeLigne = $record->fields->code_ligne;
            $pk = explode('+', $record->fields->pk)[0];

            $gares[] = [
                'gare' => $gare,
                'code_uic' => $codeUic,
                'code_ligne' => $codeLigne,
                'point_km'=> $pk
            ];
        }
        // Usort,  Outil de comparaison. Je retourne le tableau par ordre alphabétique
        //strcmp (string Comparaison) entre les deux variables
        usort($gares, function ($a, $b) {
            return strcmp($a['gare'], $b['gare']);
        });

        return response()->json($gares);
    }

    public function calculPrix(Request $request, $depart,$arrivee,$passager){

        return response()->json([
            'depart' => $depart,
            'arrivee' => $arrivee,
            'passager' => $passager
        ]);

    }

}
