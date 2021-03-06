
<div class="header-car">
    <h5 class="subtitle-client">{{ __('clients.data_car') }}</h5>
    <div data-car="0" class="btn btn-danger delete-car0 close" aria-label="Close" onclick="removeCarForm($(this).attr('data-car'))">X</div>
</div>

<div class="content-order input-group-sm mb-3 input-client">
    <div class="input-group-prepend">
        <span class="input-group-text" id="inputGroup-sizing-sm">{{ 'Marca' }}</span>
    </div>
    <input name="car[0][marca_car]"  value="{{old('car[0][marca_car]')}}" type="text" class="form-control" oninput="this.className = ''" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
</div>

<div class="content-order input-group-sm mb-3 input-client">
    <div class="input-group-prepend">
        <span class="input-group-text" id="inputGroup-sizing-sm">{{ __('clients.model_car') }}</span>
    </div>
    <input name="car[0][model_car]"  value="{{old('car[0][model_car]')}}" type="text" class="form-control" oninput="this.className = ''" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
</div>

<div class="content-order input-group-sm mb-3 input-client">
    <div class="input-group-prepend">
        <span class="input-group-text" id="inputGroup-sizing-sm">{{ __('clients.color_car') }}</span>
    </div>
    <input name="car[0][color_car]" value="{{old('car[0][color_car]')}}" type="text" class="form-control" oninput="this.className = ''" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
</div>

<div class="content-order input-group-sm mb-3 input-client">
    <div class="input-group-prepend">
        <span class="input-group-text" id="inputGroup-sizing-sm">{{ 'Matricula' }}</span>
    </div>
    <input name="car[0][plate_number]" value="{{old('car[0][plate_number]')}}" type="text" class="form-control" oninput="this.className = ''" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
</div>

<div class="content-order input-group-sm mb-3 input-client">
    <div class="input-group-prepend">
        <span class="input-group-text" id="inputGroup-sizing-sm">{{ __('clients.vin') }}</span>
    </div>
    <input name="car[0][vin]" value="{{old('car[0][vin]')}}" type="text" class="form-control" oninput="this.className = ''" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
</div>

<div class="content-order input-group-sm mb-3 input-client">
    <div class="input-group-prepend">
        <span class="input-group-text" id="inputGroup-sizing-sm">Confirmar matricula</span>
    </div>
    <input name="car[0][vin_clone]" value="{{old('car[0][vin_clone]')}}" type="text" class="form-control" oninput="this.className = ''" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
    <span id="car[0][error_vin]" class="hide alert-danger">La confirmacion del bastidor no coincide</span>
</div>

<div class="content-order input-group-sm mb-3 input-client">
    <div class="input-group-prepend">
        <span class="input-group-text" id="inputGroup-sizing-sm">{{ __('clients.itv') }}</span>
    </div>
    <div class="itv-input">
        <label>SI
            <input type="radio" name="car[0][itv]" value="1">
        </label>
        <label>NO
            <input type="radio" name="car[0][itv]" value="0">
        </label>
    </div>
</div>

<div class="content-order input-group-sm mb-3 input-client">
    <div class="input-group-prepend">
        <span class="input-group-text" id="inputGroup-sizing-sm">{{ __('clients.documents') }}</span>
    </div>
    <input name="car[0][documents]" value="{{old('car[0][documents]')}}" type="text" class="form-control" oninput="this.className = ''" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
</div>
