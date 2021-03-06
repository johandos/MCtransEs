<?php

namespace App\Http\Controllers;

use App\CarsPending;
use App\Countries;
use App\Customer;
use App\Http\Requests\OrderRequest;
use App\InformationCar;
use App\LoadOrders;
use App\OrderCMR;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade as PDF;

class LoadOrdersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->only('index', 'cmr', 'pending', 'consultCarsPending',
            'carsPending', 'carsOldLoad', 'listOrders', 'listByCountry');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Factory|View
     */
    public function index()
    {
        return view('load-orders.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Support\Collection
     */
    public function listOrders()
    {
        return DB::table('information_car as car')
            ->select('car.id as car_id', 'car.model_car', 'car.vin',
                'customer.signing', 'data_load.city_load', 'customer.phone', 'car.created_at as car_created_at',
                'data_download.contact_download', 'countries.*', 'load_orders.hash as hash',
                'load_orders.id as order_id')
            ->join('customer', 'customer.id', '=', 'customer_id')
            ->join('load_orders', 'load_orders.id', '=', 'car.')
            ->join('data_download', 'data_download.load_orders_id', '=', 'load_orders.id')
            ->join('data_load', 'data_load.load_orders_id', '=', 'load_orders.id')
            ->join('countries', 'countries.id', '=', 'data_load.countries_id')
            ->where('car.status', true)
            ->where('process_finish', '=', true)
            ->orderByDesc('car.created_at')
            ->get();
    }

    /**
     * Display a listing of the resource.
     *
     * @param $country
     * @return \Illuminate\Support\Collection
     */
    public function filterCountry($country)
    {
        $listCountries =  DB::table('information_car as info_cars')
            ->select('info_cars.*', 'order.id as order_id', 'order.*',
                'cdown.country as country_client', 'download.city_download as city_client',
                'download.contact_download as client', 'download.city_download as destino', 'load.*',
                'cload.country as country_load', 'load.city_load as city_load')
            ->join('customer', 'customer.id', '=', 'info_cars.customer_id')
            ->join('load_orders as order', 'order.id', '=', 'info_cars.load_orders_id')
            ->join('data_download as download', 'download.load_orders_id', '=', 'order.id')
            ->join('data_load as load', 'load.load_orders_id', '=', 'order.id')
            ->join('countries as cdown', 'cdown.id', '=', 'download.countries_id')
            ->join('countries as cload', 'cload.id', '=', 'load.countries_id');

        if ($country !== '0'){
            $listCountries->where('load.countries_id', $country);
        }

        $listCountries->where('order.status', true)
            ->orderByDesc('order.created_at')
            ->get();

        return $listCountries->get();
    }

    /**
     * Display a listing of the resource.
     *
     * @param $country
     * @return Factory|View
     */
    public function listByCountry($country)
    {
        return view('load-orders.order-country');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Support\Collection
     */
    public function consultCarsPending()
    {
        return DB::table('information_car as car')
            ->select('car.id as car_id', 'car.model_car', 'car.vin',
                'customer.signing', 'data_load.city_load', 'customer.phone', 'car.created_at as car_created_at',
                'data_download.contact_download', 'countries.*', 'load_orders.hash as hash',
                'load_orders.id as order_id')
            ->join('customer', 'customer.id', '=', 'customer_id')
            ->join('load_orders', 'load_orders.id', '=', 'car.load_orders_id')
            ->join('data_download', 'data_download.load_orders_id', '=', 'load_orders.id')
            ->join('data_load', 'data_load.load_orders_id', '=', 'load_orders.id')
            ->join('countries', 'countries.id', '=', 'data_load.countries_id')
            ->where('car.status', true)
            ->where('is_pending', '=', true)
            ->where('process_finish', '=', false)
            ->whereNotNull('hash')
            ->orderByDesc('car.created_at')
            ->get();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Factory|View
     */
    public function carsPending()
    {
        $load_orders = LoadOrders::all()->where('status', true);
        return view('load-orders.cars-pending', compact('load_orders'))
            ->with('i', 0);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Factory|View
     */
    public function carsOldLoad()
    {
        return view('load-orders.cars-old-load');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Support\Collection
     */
    public function consultCarsOldLoad()
    {
        return DB::table('information_car as car')
            ->select('car.id as car_id', 'car.model_car', 'car.vin',
                'customer.signing', 'data_load.city_load', 'customer.phone', 'car.created_at as car_created_at',
                'data_download.contact_download', 'countries.*', 'load_orders.hash as hash',
                'load_orders.id as order_id')
            ->join('customer', 'customer.id', '=', 'customer_id')
            ->join('load_orders', 'load_orders.id', '=', 'car.load_orders_id')
            ->join('data_download', 'data_download.load_orders_id', '=', 'load_orders.id')
            ->join('data_load', 'data_load.load_orders_id', '=', 'load_orders.id')
            ->join('countries', 'countries.id', '=', 'data_load.countries_id')
            ->where('car.status', true)
            ->where('is_pending', '=', false)
            ->where('process_finish', '=', false)
            ->orderByDesc('car.created_at')
            ->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Factory|View
     */
    public function create()
    {
        $countries = Countries::all()->pluck('country', 'id');
        return view('load-orders.create', compact('countries'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $loadOrder = LoadOrders::createAllLoadOrder($request->all(), false);

        if ($loadOrder){
            if(Auth::id()){
                return redirect()->action('LoadOrdersController@carsPending');
            }

            $hash = $loadOrder->hash;
            $car = InformationCar::findBy($request->car[0]["vin"]);
            $car = $car->id;
            return redirect()->action('LoadOrdersController@show', compact('hash', 'car'));
        }

        return redirect()
            ->back()
            ->withInput()
            ->withErrors(['Lo sentimos ocurrio un error al caragar los datos, porfavor intenta de nuevo']);
    }

    /**
     * Display the specified resource.informationCar
     *
     * @param $hash
     * @param $car
     * @return Factory|View
     */
    public function show($hash, $car)
    {
        $loadOrder = LoadOrders::assignHash($hash);
        $infoArray = LoadOrders::arrayInfo([
            'infoCars' => $loadOrder->customer
                ->infoCars->where('load_orders_id', $loadOrder->id)->first(),
            'client' => $loadOrder->customer->toArray(),
            'load_order' => $loadOrder->toArray(),
            'data_download' => $loadOrder->data_download->toArray(),
            'data_load' => $loadOrder->data_load->toArray(),
        ]);

        return view('load-orders.show', compact('infoArray'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $hash
     * @param $car
     * @return Factory|View
     */
    public function edit($hash, $car)
    {
        $loadOrder = LoadOrders::assignHash($hash);
        $infoArray = LoadOrders::arrayInfo([
            'infoCars' => $loadOrder->customer
                ->infoCars->where('load_orders_id', $loadOrder->id)->first(),
            'client' => $loadOrder->customer->toArray(),
            'load_order' => $loadOrder->toArray(),
            'data_download' => $loadOrder->data_download->toArray(),
            'data_load' => $loadOrder->data_load->toArray(),
        ]);

        $infoArray['car_id'] = $car;
        $infoArray['data_download']['country_download'] = $loadOrder->data_download->load('countries')->country;
        $infoArray['data_load']['country_load'] = $loadOrder->data_download->load('countries')->country;
        $infoArray['data_load']['date_load'] = $loadOrder->data_load->date_load;
        $infoArray['data_load']['phone_load'] = $loadOrder->data_load->phone_load;
        return view('load-orders.edit', compact('infoArray'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  $loadOrder
     * @return Response
     */
    public function update(Request $request, $loadOrder)
    {
        LoadOrders::createAllLoadOrder($request->all(), true, $loadOrder);

        return \response('ok', 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  LoadOrders $loadOrders
     * @return Factory|View
     */
    public function cmr(LoadOrders $loadOrders)
    {
        return view('load-orders.show-cmr', compact('loadOrders'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param CarsPending $carsPending
     * @return Factory|View
     */
    public function pending(CarsPending $carsPending)
    {
        return view('load-orders.pending', compact('carsPending'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param OrderCMR $cmrGenerate
     * @return Factory|Application|RedirectResponse|Redirector|View
     */
    public function cmrGenerate(OrderCMR $cmrGenerate)
    {
        $cars = explode(",", $cmrGenerate->array_cars);
        if ($cars[0] === ""){
            return redirect(\route("load-orders.carsPending"));
        }
        $loadOrders = InformationCar::query()->find($cars[0])->load_orders_id;
        $loadOrders = LoadOrders::query()->find($loadOrders);

        $infoCars = [];
        foreach ($cars as $car){
            $infoCars[] = InformationCar::query()->find($car);
        }

        return view('load-orders.generateCmr', compact('infoCars','loadOrders'));
    }

    public function pendingCars(Request $request)
    {
        $carsId = '';
        foreach ($request->cars as $car){
            if ($carsId === ''){
                $carsId = $car['car_id'];
            }else{
                $carsId = $carsId.','.$car['car_id'];
            }
        }
        $carsPending = new CarsPending();
        $carsPending->array_cars = $carsId;
        $carsPending->user_id = auth()->id();
        $carsPending->save();

        return route('load-orders.pending-cars', $carsPending->id);
    }

    public function generateCMR(Request $request)
    {
        $carsId = '';
        foreach ($request->cars as $car){
            if ($carsId === ''){
                $carsId = $car['car_id'];
            }else{
                $carsId = $carsId.','.$car['car_id'];
            }
        }
        $generateCMR = new OrderCMR();
        $generateCMR->array_cars = $carsId;
        $generateCMR->enrollment = 0;
        $generateCMR->user_id = auth()->id();
        $generateCMR->save();

        return route('load-orders.cmrGenerate', $generateCMR->id);
    }

    public function sendCollected(Request $request){
        $infocar = InformationCar::query()->find($request->car_id);
        $infocar->is_pending = false;
        $infocar->save();

        return \response()->json($infocar, 200);
    }

    public function sendCollectedFinish(Request $request){
        $infocar = InformationCar::query()->find($request->car_id);
        $infocar->process_finish = true;
        $infocar->save();

        return \response()->json($infocar, 200);
    }

    public function pendingApiCars(CarsPending $carsPending)
    {
        $showPending = explode(",", $carsPending->array_cars);
        $cars = [];
        foreach ($showPending as $keyLoad => $car){
            $car = InformationCar::query()->find($car);
            $loadOrder = $car->loadOrder;
            $cars[$keyLoad]['client']             = $loadOrder->data_download->contact_download;
            $cars[$keyLoad]['buyer']              = $loadOrder->bill_to;
            $cars[$keyLoad]['action_do']          = 'DESCARGAR';
            $cars[$keyLoad]['car'][0]               = $car->model_car .",". $car->vin;
            $cars[$keyLoad]['addresses_load']     = $loadOrder->data_load->addresses_load.",". 'Codigo postal: '
                .$loadOrder->data_load->postal_cod_load.",". 'Ciudad: '
                .$loadOrder->data_load->city_load.'' ;
            $cars[$keyLoad]['scheduler']          = '';
            $cars[$keyLoad]['addresses_download'] = $loadOrder->data_download->addresses_download.",". 'Codigo postal: '
                .$loadOrder->data_download->postal_cod_download.",". 'Ciudad: '
                .$loadOrder->data_download->city_download.'' ;
            $cars[$keyLoad]['contact']            = $loadOrder->data_download->contact_download.",".$loadOrder->data_download->mobile_download;
            $cars[$keyLoad]['observation']        = $loadOrder->price;
        }

        return $cars;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $hash
     * @return JsonResponse
     */
    public function destroy($hash)
    {
        $loadOrder = LoadOrders::assignHash($hash);
        $loadOrder->status = 0;
        foreach ($loadOrder->customer->infoCars as $infoCars){
            $infoCars->status = 0;
            $infoCars->save();
        }

        $loadOrder->save();
        return \response()->json('ok');
    }

    public function filter($filter){
        if (strlen($filter) > 2){
            $filter = strtolower($filter);
            return Customer::query()
                ->select('order.*', 'customer.*', 'order.id as order_id', 'order.*', 'download.*')
                ->join('load_orders as order', 'customer.id', '=', 'order.customer_id')
                ->join('data_download as download', 'download.load_orders_id', '=', 'order.id')
                ->join('data_load as load', 'load.load_orders_id', '=', 'order.id')
                ->join('countries as cdown', 'cdown.id', '=', 'download.countries_id')
                ->join('countries as cload', 'cload.id', '=', 'load.countries_id')
                ->whereRaw('lower(import_company) like (?)',["%{$filter}%"])
                ->orWhereRaw('lower(bill_to) like (?)',["%{$filter}%"])
                ->orderBy('bill_to')
                ->get();
        }
        return '';
    }

    public function getFilter($filter){
        return Customer::query()
            ->select('order.*', 'customer.*', 'order.id as order_id', 'order.*', 'download.*')
            ->join('load_orders as order', 'customer.id', '=', 'order.customer_id')
            ->join('data_download as download', 'download.load_orders_id', '=', 'order.id')
            ->join('data_load as load', 'load.load_orders_id', '=', 'order.id')
            ->join('countries as cdown', 'cdown.id', '=', 'download.countries_id')
            ->join('countries as cload', 'cload.id', '=', 'load.countries_id')
            ->where('order.id', $filter)
            ->get();
    }

    public function listCountry(){
        return DB::table('customer')->pluck('city')->toArray();
    }

    public function cmrPDF(Request $request){
        $matricula = $request->matricula;
        $date = $request->date;
        $typeCoche = $request->typeCoche;
        $loadOrders = LoadOrders::query()->find($request->loadOrder);
        $customer = $loadOrders->customer;
        $download = $loadOrders->data_download;
        $load = $loadOrders->data_load;
        $cars = $loadOrders->customer->infoCars;
        $pdf = PDF::loadView('load-orders.pdf-cmr', compact('loadOrders', 'matricula', 'date', 'typeCoche',
            'customer', 'download', 'load', 'cars'));
        return $pdf->download($download->contact_download.'_'.$date.'_CMR.pdf');
    }

    public function cmrWord(Request $request){
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();

        $matricula = $request->matricula;
        $date = $request->date;
        $typeCoche = $request->typeCoche;
        $loadOrders = LoadOrders::query()->find($request->loadOrder);
        $customer = $loadOrders->customer;
        $download = $loadOrders->data_download;
        $load = $loadOrders->data_load;
        $cars = $loadOrders->customer->infoCars;

        $fontStyle = new \PhpOffice\PhpWord\Style\Font();
        $fontStyle->setBold(true);
        $fontStyle->setName('Tahoma');
        $fontStyle->setSize(12);
        $section->addText($customer->signing);
        $section->addText($load->addresses_load);
        $section->addText($load->city_load .'//'. $load->postal_cod_load);
        $section->addTextBreak(2);
        $section->addText($loadOrders->bill_to);
        $section->addText($download->addresses_download);
        $section->addText($download->city_download .'//'. $download->postal_cod_download);
        $section->addText($matricula);
        $section->addTextBreak(2);
        $section->addText($download->addresses_download);
        $section->addText($download->city_download);
        $section->addText($download->postal_cod_download);
        $section->addTextBreak(2);
        $section->addText($load->date_load);
        $section->addText($load->addresses_load);
        $section->addText($load->city_load .'//'. $load->postal_cod_load);
        $section->addText($typeCoche);
        $section->addTextBreak(2);
        foreach ($cars as $car){
            $section->addText($car->model_car.'    '.$car->vin);
        }
        $section->addText($load->city_load);
        $section->addText(isset($date) ? $date : $load->date_load);
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save('helloWorld.docx');
    }

    public function requestPrint($request){
        $info = '';
        $matricula = $request->matricula;
        $date = $request->date;
        $typeCoche = $request->typeCoche;
        $loadOrders = LoadOrders::query()->find($request->loadOrder);
        $customer = $loadOrders->customer;
        $download = $loadOrders->data_download;
        $load = $loadOrders->data_load;
        $cars = $loadOrders->customer->infoCars;

        return $info;
    }
}
