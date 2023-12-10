<?php

namespace App\Http\Controllers;

use Chiiya\Passes\Google\Components\Common\Barcode;
use Chiiya\Passes\Google\Components\Common\LocalizedString;
use Chiiya\Passes\Google\Components\Common\Uri;
use Illuminate\Http\Request;
use Chiiya\Passes\Google\Components\Common\DateTime;
use Chiiya\Passes\Google\Components\Common\GroupingInfo;
use Chiiya\Passes\Google\Components\Common\Image;
use Chiiya\Passes\Google\Components\Common\LinksModuleData;
use Chiiya\Passes\Google\Components\Common\TextModuleData;
use Chiiya\Passes\Google\Components\Common\TimeInterval;
use Chiiya\Passes\Google\Enumerators\BarcodeRenderEncoding;
use Chiiya\Passes\Google\Enumerators\BarcodeType;
use Chiiya\Passes\Google\Enumerators\MultipleDevicesAndHoldersAllowedStatus;
use Chiiya\Passes\Google\Enumerators\State;
use Chiiya\Passes\Google\Http\GoogleClient;
use Chiiya\Passes\Google\JWT;
use Chiiya\Passes\Google\Passes\GenericClass;
use Chiiya\Passes\Google\Passes\GenericObject;
use Chiiya\Passes\Google\Repositories\GenericClassRepository;
use Chiiya\Passes\Google\ServiceCredentials;
use Illuminate\Support\Str;

class GooglePassGeneratorController extends Controller
{
    public function generatePass(Request $request)
    {
        $data = $request->all();

        $object = new GenericObject(
            classId: $data['classId']. '_' . time(),
            id: $data['id']. '_' . time(),
            state: State::ACTIVE, // Set the state property
            cardTitle: new LocalizedString(['defaultValue' => ['language' => 'en-US', 'value' => $data['cardTitle']['defaultValue']['value']]]),
            subheader: new LocalizedString(['defaultValue' => ['language' => 'en-US', 'value' => $data['subheader']['defaultValue']['value']]]),
            header: new LocalizedString(['defaultValue' => ['language' => 'en-US', 'value' => $data['header']['defaultValue']['value']]]),
            barcode: new Barcode([
                'type' => $data['barcode']['type'],
                'value' => $data['barcode']['value'],
                'alternateText' => $data['barcode']['alternateText'],
            ]),
            hexBackgroundColor: $data['hexBackgroundColor'],
            logo: new Image([
                'sourceUri' => $data['logo']['sourceUri'],
            ]),
            heroImage: new Image([
                'sourceUri' => $data['heroImage']['sourceUri'],
            ]),
        );
        $credentials = ServiceCredentials::parse('service_credentials.json');
        $client = GoogleClient::createAuthenticatedClient($credentials);

        $repository = new GenericClassRepository($client);
        $textModulesData = [];
        foreach ($data['textModulesData'] as $textModule) {
            $textModulesData[] = new TextModuleData([
                'id' => $textModule['id'],
                'header' => $textModule['header'],
                'body' => $textModule['body'],
            ]);
        }

        $class = new GenericClass(
            id: $data['classId'] . '_' . time(),
            textModulesData: $textModulesData,
            multipleDevicesAndHoldersAllowedStatus: MultipleDevicesAndHoldersAllowedStatus::MULTIPLE_HOLDERS,
        );

        $repository->create($class);

        $jwt = (new JWT([
            'iss' => $credentials->client_email,
            'key' => $credentials->private_key,
            'origins' => ['https://example.org'],
        ]))->addGenericObject($object)->sign();

        return "https://pay.google.com/gp/v/save/{$jwt}";
    }
}
