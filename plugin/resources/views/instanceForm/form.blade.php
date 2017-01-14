<link href="/mod/charon/plugin/public/css/instanceForm.css" rel="stylesheet">

<div id="app">

    <form-fieldset>
        <template slot="title">{{ translate('naming') }}</template>

        <slot>

            <charon-text-input
                    input_name="name"
                    input_label="{{ translate('task_name') }}"
                    required="true"
                    input_value="{{ isset($charon) ? $charon->name : '' }}">
            </charon-text-input>

        </slot>
    </form-fieldset>

</div>

<script src="/mod/charon/plugin/public/js/app.js"></script>
