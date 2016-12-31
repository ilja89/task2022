<link href="/mod/charon/plugin/public/{{ elixir('css/instanceForm.css') }}" rel="stylesheet">

@include('form.partials._fieldset', [
    'fieldset_title' => translate('naming'),
    'fields' => [
        [
            'template_name' => 'form.partials._text_input',
            'input_label' => translate('task_name'),
            'input_name' => 'name',
            'input_value' => isset($charon) ? $charon->name : '',
            'required' => true
        ]
    ]
])
