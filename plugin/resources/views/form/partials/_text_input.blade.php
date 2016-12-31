<div id="id_{{ $input_name }}_container" class="fcontainer clearfix">
    <div id="fitem_id_{{ $input_name }}" class="fitem {{ $required ? 'required' : '' }} fitem_ftext ">
        <div class="fitemtitle">
            <label for="id_{{ $input_name }}" class="{{ $required ? 'required' : '' }}">{{ $input_label }}</label>
        </div>
        <div class="felement ftext">
            <input size="64" name="{{ $input_name }}" type="text" {{ $required ? 'required' : '' }}
                   id="id_{{ $input_name }}" class="form-control" value="{{ $input_value }}">
        </div>
    </div>
</div>
