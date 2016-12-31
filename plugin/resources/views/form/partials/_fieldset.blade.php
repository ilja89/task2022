<fieldset class="clearfix collapsible" id="id_modstandardelshdr">

    <legend class="ftoggler">{{ $fieldset_title }}</legend>

    @foreach($fields as $field)
        @include($field['template_name'], [
            'input_label' => $field['input_label'],
            'input_name' => $field['input_name'],
            'input_value' => $field['input_value'],
            'required' => $field['required']
        ])
    @endforeach

</fieldset>
