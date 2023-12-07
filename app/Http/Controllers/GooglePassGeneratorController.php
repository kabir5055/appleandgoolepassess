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
        // Provided JSON structure
        $data = $request->all();
// '{
//          "id": "3388000000022301125.coupon15",
//          "classId": "3388000000022301125.coupon15",
//          "logo": {
//            "sourceUri": {
//              "uri": "https://storage.googleapis.com/wallet-lab-tools-codelab-artifacts-public/pass_google_logo.jpg"
//            },
//            "contentDescription": {
//              "defaultValue": {
//                "language": "en-US",
//                "value": "LOGO_IMAGE_DESCRIPTION"
//              }
//            }
//          },
//          "cardTitle": {
//            "defaultValue": {
//              "language": "en-US",
//              "value": "TEST"
//            }
//          },
//          "subheader": {
//            "defaultValue": {
//              "language": "en-US",
//              "value": "Attendee"
//            }
//          },
//          "header": {
//            "defaultValue": {
//              "language": "en-US",
//              "value": "Alex McJacobs"
//            }
//          },
//          "textModulesData": [
//            {
//              "id": "points",
//              "header": "POINTS",
//              "body": "1112"
//            },
//            {
//              "id": "contacts",
//              "header": "CONTACTS",
//              "body": "79"
//            }
//          ],
//          "barcode": {
//            "type": "QR_CODE",
//            "value": "https://luminouslabsbd.com/",
//            "alternateText": ""
//          },
//          "hexBackgroundColor": "#4285f4",
//          "heroImage": {
//            "sourceUri": {
//              "uri": "https://storage.googleapis.com/wallet-lab-tools-codelab-artifacts-public/google-io-hero-demo-only.png"
//            },
//            "contentDescription": {
//              "defaultValue": {
//                "language": "en-US",
//                "value": "HERO_IMAGE_DESCRIPTION"
//              }
//            }
//          }
//        }';

//        $data = json_decode($json, true);

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
//    public function generatePass()
//    {
//        // Provided JSON structure
//
////        $img = asset('pass_google_logo.jpg');
////        $img2 = asset('google-io-hero-demo-only.png');
//        $img = "https://storage.googleapis.com/wallet-lab-tools-codelab-artifacts-public/pass_google_logo.jpg";
//        $img2 = "https://storage.googleapis.com/wallet-lab-tools-codelab-artifacts-public/google-io-hero-demo-only.png";
////        dd($img);
//
//        $json = '{
//          "id": "3388000000022301125.coupon15",
//          "classId": "3388000000022301125.coupon15",
//          "logo": {
//            "sourceUri": {
//              "uri": "' . $img . '"
//            },
//            "contentDescription": {
//              "defaultValue": {
//                "language": "en-US",
//                "value": "A"
//              }
//            }
//          },
//          "cardTitle": {
//            "defaultValue": {
//              "language": "en-US",
//              "value": "[TEST ONLY] Google I/O"
//            }
//          },
//          "subheader": {
//            "defaultValue": {
//              "language": "en-US",
//              "value": "Attendee"
//            }
//          },
//          "header": {
//            "defaultValue": {
//              "language": "en-US",
//              "value": "MR.X"
//            }
//          },
//          "textModulesData": [
//            {
//              "id": "points",
//              "header": "POINTS",
//              "body": "1112"
//            },
//            {
//              "id": "contacts",
//              "header": "CONTACTS",
//              "body": "79"
//            }
//          ],
//          "barcode": {
//            "type": "QR_CODE",
//            "value": "https://luminouslabsbd.com/",
//            "alternateText": ""
//          },
//          "hexBackgroundColor": "#4285f4",
//          "heroImage": {
//            "sourceUri": {
//              "uri": "' . $img2 . '"
//            },
//            "contentDescription": {
//              "defaultValue": {
//                "language": "en-US",
//                "value": "HERO_IMAGE_DESCRIPTION"
//              }
//            }
//          }
//        }';
//
//        $data = json_decode($json, true);
//        $data['logo']['sourceUri']['uri'] = $img;
//        $data['heroImage']['sourceUri']['uri'] = $img2;
////        dd($data);
//
//        $object = new GenericObject(
//            classId: $data['classId']. '_' . time(),
//            id: $data['id']. '_' . time(),
//            state: State::ACTIVE, // Set the state property
//            cardTitle: new LocalizedString(['defaultValue' => ['language' => 'en-US', 'value' => $data['cardTitle']['defaultValue']['value']]]),
//            subheader: new LocalizedString(['defaultValue' => ['language' => 'en-US', 'value' => $data['subheader']['defaultValue']['value']]]),
//            header: new LocalizedString(['defaultValue' => ['language' => 'en-US', 'value' => $data['header']['defaultValue']['value']]]),
//            barcode: new Barcode([
//                'type' => $data['barcode']['type'],
//                'value' => $data['barcode']['value'],
//                'alternateText' => $data['barcode']['alternateText'],
//            ]),
//            hexBackgroundColor: $data['hexBackgroundColor'],
//            logo: new Image([
//                'sourceUri' => $data['logo']['sourceUri'],
//            ]),
//            heroImage: new Image([
//                'sourceUri' => $data['heroImage']['sourceUri'],
//            ]),
//        );
//        $credentials = ServiceCredentials::parse('service_credentials.json');
//        $client = GoogleClient::createAuthenticatedClient($credentials);
//
//        $repository = new GenericClassRepository($client);
//        $textModulesData = [];
//        foreach ($data['textModulesData'] as $textModule) {
//            $textModulesData[] = new TextModuleData([
//                'id' => $textModule['id'],
//                'header' => $textModule['header'],
//                'body' => $textModule['body'],
//            ]);
//        }
//        $class = new GenericClass(
//            id: $data['classId'] . '_' . time(),
//            textModulesData: $textModulesData,
//            multipleDevicesAndHoldersAllowedStatus: MultipleDevicesAndHoldersAllowedStatus::MULTIPLE_HOLDERS,
//        );
////dd($class);
//        $repository->create($class);
//
////        $object['logo']['sourceUri']['uri'] = $img;
////        $object['heroImage']['sourceUri']['uri'] = $img2;
//
////        dd($object);
//
//        $jwt = (new JWT([
//            'iss' => $credentials->client_email,
//            'key' => $credentials->private_key,
//            'origins' => ['https://example.org'],
//        ]))->addGenericObject($object)->sign();
//
//        return "https://pay.google.com/gp/v/save/{$jwt}";
//    }
}
