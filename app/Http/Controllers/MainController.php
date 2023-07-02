<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MainController extends Controller
{
    public function registroCliente(Request $request)
    {
        return $this->cast('registroCliente', $request->only([
            'documento',
            'nombres',
            'correo',
            'celular'
        ]));
    }

    public function recargaBilletera(Request $request)
    {
        return $this->cast('recargaBilletera', $request->only([
            'documento',
            'celular',
            'valor'
        ]));
    }

    public function pagar(Request $request)
    {
        return $this->cast('pagar', $request->only([
            'documento',
            'celular',
            'valor'
        ]));
    }

    public function confirmarPago(Request $request)
    {
        return $this->cast('confirmarPago', $request->only([
            'id',
            'token'
        ]));
    }

    public function consultarSaldo(Request $request)
    {
        return $this->cast('consultarSaldo', $request->only([
            'documento',
            'celular'
        ]));
    }

    private function cast(string $method, array $args)
    {
        $soapUrl = env('SOAP_URL');

        $content = '';
        foreach ($args as $key => $arg) {
            $content .= "<$key>$arg</$key>\n";
        }

        $xmlPayload = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:soap=\"$soapUrl\">
                    <soapenv:Header/>
                    <soapenv:Body>
                        <soap:$method>
                            $content
                        </soap:$method>
                    </soapenv:Body>
                </soapenv:Envelope>";

        $headers = [
            'Content-Type: text/xml',
            'Content-Length: ' . strlen($xmlPayload),
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $soapUrl);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xmlPayload);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            return response()->json(['error' => $error], 500);
        }
        curl_close($curl);

        return $response;
    }
}
