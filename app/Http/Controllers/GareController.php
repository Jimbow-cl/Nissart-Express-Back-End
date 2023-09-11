<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                'point_km' => $pk
            ];
        }
        // Usort,  Outil de comparaison. Je retourne le tableau par ordre alphabétique
        //strcmp (string Comparaison) entre les deux variables
        usort($gares, function ($a, $b) {
            return strcmp($a['gare'], $b['gare']);
        });

        return response()->json($gares);
    }

    public function calculPrix(Request $request)
    {
        if ($request->query('passenger') == null) {
            $passenger=1;
        };
        // utilisation des Query Params à la place des Pass Params

        $start = $request->query('start');
        $end = $request->query('end'); 
        $passenger= $request->query('passenger');
        $date = $request->query('date');
        $voucher = 0;
        $user_id = Auth::id();
        if ($user_id != null) {
            $voucher = Voucher::where('user_id', $user_id)->first();
            // Verification si il existe une Réduction à l'utilisateur:
            if ($voucher == null) {
                $voucher = 0;
            } else {
                $voucher = $voucher->value;
            }
        }


        $response = $this->appelGare(); // Appel de la fonction appelGare
        $stations = json_decode($response->getContent(), true);

        $startStation = null;
        $endStation = null;

        // Parcourir le tableau des gares pour trouver les gares correspondantes
        foreach ($stations as $station) {
            if ($station['code_uic'] === $start) {
                $startStation = $station;
                // 4 Lignes existes,calcule du Pk pour enlever le nombre de km de départ pour le prix
                if ($startStation['code_ligne'] === "930000") {
                    $startRef = $startStation['point_km'] - 184;
                }
                if ($startStation['code_ligne'] === "944000") {
                    $startRef = $startStation['point_km'] - 2;
                }
                if ($startStation['code_ligne'] === "945000") {
                    $startRef = $startStation['point_km'] - 9;
                }
                if ($startStation['code_ligne'] === "946000") {
                    $startRef = $startStation['point_km'] - 37;
                }
            }
            if ($station['code_uic'] === $end) {
                $endStation = $station;
                // 4 Lignes existes,calcule du Pk pour enlever le nombre de km de départ pour le prix
                if ($endStation['code_ligne'] === "930000") {
                    $endRef = $endStation['point_km'] - 184;
                }
                if ($endStation['code_ligne'] === "944000") {
                    $endRef = $endStation['point_km'] - 2;
                }
                if ($endStation['code_ligne'] === "945000") {
                    $endRef = $endStation['point_km'] - 9;
                }
                if ($endStation['code_ligne'] === "946000") {
                    $endRef = $endStation['point_km'] - 37;
                }
            }
        }
        $differenceKm = abs($endRef - $startRef);
        $priceKm = 0.2;
        $totalPriceBfVoucher = $differenceKm * $priceKm;

        // Calcul du montant de réduction
        if ($voucher != 0) {
            $priceVoucher = $totalPriceBfVoucher * $voucher / 100;
        } else {
            $priceVoucher = 0;
        }

        // Calcul du prix total après réduction et ajout des passagers
        $totalPrice = ($totalPriceBfVoucher * $passenger) - $priceVoucher;
        $totalbf = ($totalPriceBfVoucher * $passenger);
        // je préviens juste au cas où le prix est inférieur, d'un minimum de 0€
        if ($totalPrice < 0) {
            $totalPrice = 0;
        }
        $totalPrice = round($totalPrice, 2);

        //Calcul de la première classe 
        $prix1st = $totalPrice * 1.4;
        $prix1st = round($prix1st, 2);

        //Calcul de la troisieme classe 
        $pri3xrd = $totalPrice * 0.8;
        $pri3xrd = round($pri3xrd, 2);

        //Calcul du prix total Premiere
        $totalbf1 = $totalbf * 1.4;
        $totalbf1 = round($totalbf1, 2);

        //Calcul du prix total Seconde
        $totalbf2 = $totalbf;
        $totalbf2 = round($totalbf2, 2);

        //Calcul du prix total Troisieme
        $totalbf3 = $totalbf * 0.8;
        $totalbf3 = round($totalbf3, 2);
        //Calcul des reductions
        $priceVoucherthree = round(($priceVoucher * 0.8), 2);
        $priceVouchertwo = round($priceVoucher, 2);
        $priceVoucherone = round(($priceVoucher * 1.4), 2);
        return response()->json([
            'depart' => $startStation['gare'],
            'arrivee' => $endStation['gare'],
            'date' => $date,
            'passager' => $passenger,
            'discountone' => $priceVoucherone,
            'discounttwo' => $priceVouchertwo,
            'discountthree' => $priceVoucherthree,
            'totalbfone' => $totalbf1,
            'totalbftwo' => $totalbf2,
            'totalbfthree' => $totalbf3,
            'prix1st' => $prix1st,
            'prix2nd' => $totalPrice,
            'prix3rd' => $pri3xrd,
        ]);
    }
}
