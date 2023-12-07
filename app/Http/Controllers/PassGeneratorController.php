<?php

namespace App\Http\Controllers;

use Chiiya\Passes\Apple\Passes\Coupon;
use Chiiya\Passes\Apple\Components\Barcode;
use Chiiya\Passes\Apple\Components\Field;
use Chiiya\Passes\Apple\Components\SecondaryField;
use Chiiya\Passes\Apple\Enumerators\ImageType;
use Chiiya\Passes\Apple\Components\Image;
use Chiiya\Passes\Apple\PassFactory;
use Illuminate\Http\Request;
use ZipArchive;

class PassGeneratorController extends Controller
{
    public function generatePass(Request $request)
    {
        $request->validate([
            'headerFields' => 'array',
            'secondaryFields' => 'array',
            'backFields' => 'array',
        ]);

        $headerFields = $request->input('headerFields', []);
        $secondaryFields = $request->input('secondaryFields', []);
        $backFields = $request->input('backFields', []);

        $pass = new Coupon(
            description: '15% off purchases',
            organizationName: 'ACME',
            passTypeIdentifier: 'pass.com.example.passgenerator',
            serialNumber: '125478963221',
            teamIdentifier: 'X8M4TB32RP',
            authenticationToken: 'jTjIrW0sePoLWQgOzZBBkHBEsw8dKG',
            backgroundColor: 'rgb(0, 0, 0)',
            foregroundColor: 'rgb(255, 255, 255)',
            labelColor: 'rgb(255, 255, 255)',
            logoText: 'ACME',
            expirationDate: '2024-01-01T00:00:00+06:00',
            barcode: [
                'format' => 'PKBarcodeFormatQR',
                'message' => 'Hello',
                'messageEncoding' => 'utf-8',
            ],
            headerFields: $headerFields,
            secondaryFields: $secondaryFields,
            backFields: $backFields,
        );

        $pass
            ->addImage(new Image(public_path('img/logo.png'), ImageType::LOGO, 1))
            ->addImage(new Image(public_path('img/icon.png'), ImageType::ICON, 2))
            ->addImage(new Image(public_path('img/strip.png'), ImageType::STRIP, 3));


        $pass_identifier = mt_rand(100000, 999999);
        $passFileName = "pass_$pass_identifier";

        $outputPath = storage_path('app/public/pass');

        $factory = new PassFactory();
        $factory->setCertificate(storage_path('app/certificates/certificates.p12'));
        $factory->setPassword('passgen12');
        $factory->setWwdr(storage_path('app/certificates/example_passgenerator.pem'));
        $factory->setOutput($outputPath);

        $zip = new ZipArchive();
        $zipPath = $outputPath . '/' . $passFileName . 'zip';

        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            dd($zip->status, $zip->getStatusString());
            return response()->json(['error' => 'Failed to open ZipArchive']);
        }

        $zip->close();

        $factory->create($pass, $passFileName);

        $headers = [
            'Content-Type' => 'application/vnd.apple.pkpass',
            'Content-Disposition' => 'attachment; filename="' . $passFileName . '.pkpass"',
        ];

        $passFilePath = $outputPath . '/' . $passFileName . '.pkpass';

        if (file_exists($passFilePath)) {
            return response()->download($passFilePath, $passFileName . '.pkpass', $headers);
        } else {
            return response()->json(['error' => 'Failed to generate pass file']);
        }
    }
}
