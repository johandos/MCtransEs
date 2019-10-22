<?php

namespace App\Http\Controllers;

use App\Bills;
use App\Customer;
use App\InformationCar;
use App\LoadOrders;
use App\Services;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BillsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $bills = Bills::all();
        return view('bills.index', compact('bills'))
            ->with('i', 0);
    }
    /**
     * Display a listing of the resource.
     **
     * @param  LoadOrders $loadOrder
     * @return Response
     */
    public function showBillLoadOrder(LoadOrders $loadOrder)
    {
        $bill = $loadOrder->bill;
        $service = Services::all()->find(1);
        return view('bills.show', compact('bill', 'loadOrder', 'service'))
            ->with('date', Carbon::now()->format('d/m/Y'));
    }

    public function billLoadOrder(LoadOrders $loadOrder){
        $bill = $loadOrder->bill;
        $service = Services::all()->find(1);
        $date = Carbon::now()->format('d/m/Y');
        $cars = $loadOrder->customer->infoCars->where('status', 1);

        return ['bill' => $bill, 'services' => $service, 'date' => $date, 'cars' => $cars];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  $bill
     * @return Response
     */
    public function update(Request $request, $bill)
    {
        $loadOrder = LoadOrders::all()->find($request->load_id);

        $bill = Bills::all()->find($loadOrder->bill->id);
        $bill->num_bill = $request->numBill;
        $bill->name_client = $request->name_client;
        $bill->address_client = $request->address_client;
        $bill->department_client = $request->department;
        $bill->city_client = $request->city_client;
        $bill->postal_cod_client = $request->postal_cod_client;
        $bill->description = $request->description_bill;
        $bill->unit_price = $request->unit_price;
        $bill->price = $request->price;
        $bill->iva = $request->iva_bill;
        $bill->observations = $request->observations_bill;
        $bill->cif = $request->cif;
        $bill->save();

        foreach ($request->cars as $car){
            $infoCar = InformationCar::all()->find($car['id']);

            $infoCar->model_car = $car['model_car'];
            $infoCar->save();
        }

        $service = Services::all()->find(1);
        $service->precio = $bill->unit_price;
        $service->save();

        return \response('ok', 200);
    }
}