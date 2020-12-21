<?php

namespace App;

use Carbon\Carbon;
use http\Client;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LoadOrders
 * @package App
 */
class LoadOrders extends Model
{
    /**
     * @var string
     */
    protected $table = 'load_orders';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo('App\Customer');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function data_download()
    {
        return $this->hasOne('App\DataDownload');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function data_load()
    {
        return $this->hasOne('App\DataLoad');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function bill()
    {
        return $this->hasOne('App\Bills');
    }

    /**
     * @param $hash
     * @return mixed
     */
    static function assignHash($hash){
        return LoadOrders::all()->where('hash', $hash)->first();
    }

    /**
     * @param $infoArray
     * @param $edit
     * @param null $hash
     * @return LoadOrders|false|mixed
     */
    static function createAllLoadOrder($infoArray, $edit, $hash = null){
        $loadOrder = self::assignHash($hash);

        if (empty($loadOrder)){
            $loadOrder = new LoadOrders();
        }

        $dataDownload = new DataDownload();
        $dataLoad = new DataLoad();

        $client = Customer::findOrCreateClient($infoArray);

        if ($client){
            $loadOrder->customer_id = $client->id;
            $loadOrder->contact_person = $infoArray['contact_person'];
            $loadOrder->date_upload = Carbon::now();
            $loadOrder->bill_to = $infoArray['bill_to'];
            $loadOrder->price = $infoArray['price_order'];
            $loadOrder->constancy = isset($infoArray['constar_client']) ? $infoArray['constar_client'] : '.';
            $loadOrder->payment_type_other = isset($infoArray['otrosInput']) ? $infoArray['otrosInput'] : '.';
            $loadOrder->payment_type = $infoArray['payment_type'];
            $loadOrder->import_company = $infoArray['import_company'];
            $loadOrder->identificacion_fiscal = $infoArray['identificacion_fiscal'];
            $loadOrder->domicilio_fiscal = $infoArray['domicilio_fiscal'];
            $loadOrder->poblacion = $infoArray['poblacion'];
            $loadOrder->auto_id = $infoArray['auto_id'];
            $loadOrder->pick_up = $infoArray['pick_up'];
            $loadOrder->save();

            $loadOrder->hash = md5($loadOrder->id);
            $loadOrder->save();

            if (!empty($loadOrder) && !empty($loadOrder->data_download)){
                $dataLoad = $loadOrder->data_load;
            }

            $dataLoad->countries_id = $infoArray['country'];
            $dataLoad->addresses_load = $infoArray['addresses_load'];
            $dataLoad->city_load = $infoArray['city_load'];
            $dataLoad->date_load = $infoArray['date_load'];
            $dataLoad->postal_cod_load = $infoArray['postal_cod_load'];
            $dataLoad->phone_load = $infoArray['phone_load'];
            $dataLoad->mobile_load = $infoArray['mobile_load'];
            $dataLoad->load_orders_id = $loadOrder->id;
            $dataLoad->save();

            if (!empty($loadOrder) && !empty($loadOrder->data_download)){
                $dataDownload = $loadOrder->data_download;
            }

            $dataDownload->countries_id = $infoArray['country_download'];
            $dataDownload->addresses_download = $infoArray['addresses_download'];
            $dataDownload->city_download = $infoArray['city_download'];
            $dataDownload->postal_cod_download = $infoArray['postal_cod_download'];
            $dataDownload->contact_download = $infoArray['contact_download'];
            $dataDownload->mobile_download = $infoArray['mobile_download'];
            $dataDownload->load_orders_id = $loadOrder->id;
            $dataDownload->driver_data_id = DriverData::all()->first()->id;//isset($infoArray['data_driver']) ? $infoArray['data_driver'] : 2;
            $dataDownload->cmr = isset($infoArray['cmr']) ? $infoArray['cmr'] : " ";
            $dataDownload->observations = $infoArray['observations'];
            $dataDownload->save();

            InformationCar::findOrCreateInformationCar($client, $infoArray["car"], $loadOrder);

            if (!empty($loadOrder) && !empty($dataDownload) && !empty($dataLoad) && !empty($infoArray)){
                Bills::createBill($loadOrder, $client, $dataDownload, $infoArray['payment_type'],
                    $infoArray['identificacion_fiscal'], $infoArray['domicilio_fiscal'], $infoArray['poblacion'], $edit);
            }

            return $loadOrder;
        }

        return false;
    }

    /**
     * @param $validateInfo
     * @return array
     */
    static function arrayInfo($validateInfo){
        $infoArray = [];
        $infoArray['information_car'] = [];
        $infoArray['information_car'] = [
            'model_car'      => isset($validateInfo['infoCars']['model_car']) ? $validateInfo['infoCars']['model_car'] : '',
            'color_car'      => isset($validateInfo['infoCars']['color_car']) ? $validateInfo['infoCars']['color_car'] : '',
            'vin'            => isset($validateInfo['infoCars']['vin']) ? $validateInfo['infoCars']['vin'] : '',
            'plate_number'   => isset($validateInfo['infoCars']['plate_number']) ? $validateInfo['infoCars']['plate_number'] : '',
            'documents'      => isset($validateInfo['infoCars']['documents']) ? $validateInfo['infoCars']['documents'] : '',
        ];

        $infoArray['client'] = Customer::validateClient($validateInfo);
        $infoArray['load_order'] = LoadOrders::validateLoadOrder($validateInfo);
        $infoArray['data_load'] = DataLoad::validateDataLoad($validateInfo);
        $infoArray['data_download'] = DataDownload::validateDataDownload($validateInfo);

        return $infoArray;
    }

    /**
     * @param $info
     * @return string[]
     */
    static public function validateLoadOrder($info){
        return [
            'id'                        => isset($info['load_order']['hash']) ? $info['load_order']['hash'] : '',
            'contact_person'            => isset($info['load_order']['contact_person']) ? $info['load_order']['contact_person'] : '',
            'bill_to'                   => isset($info['load_order']['bill_to']) ? $info['load_order']['bill_to'] : '',
            'payment_type_other'        => isset($info['load_order']['payment_other']) ? $info['load_order']['payment_other'] : '',
            'constancy'                 => isset($info['load_order']['constancy']) ? $info['load_order']['constancy'] : '',
            'import_company'            => isset($info['load_order']['import_company']) ? $info['load_order']['import_company'] : '',
            'price'                     => isset($info['load_order']['price']) ? $info['load_order']['price'] : '',
            'identificacion_fiscal'     => isset($info['load_order']['identificacion_fiscal']) ? $info['load_order']['identificacion_fiscal'] : '',
            'domicilio_fiscal'          => isset($info['load_order']['domicilio_fiscal']) ? $info['load_order']['domicilio_fiscal'] : '',
            'poblacion'                 => isset($info['load_order']['poblacion']) ? $info['load_order']['poblacion'] : '',
            'payment_type'              => isset($info['load_order']['payment_type']) ? $info['load_order']['payment_type'] : '',
            'auto_id'                   => isset($info['load_order']['auto_id']) ? $info['load_order']['auto_id'] : '',
            'pick_up'                   => isset($info['load_order']['pick_up']) ? $info['load_order']['pick_up'] : '',
        ];
    }
}
