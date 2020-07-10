<template>
    <div class="deadline-row is-flex">
        <div class="deadline-field">
            <label>Start</label>
            <p class="input-helper">Start date and time.</p>
            <datepicker :datetime="deadline.deadline_time"></datepicker>
            <input type="hidden" :value="deadline.deadline_time.time">
        </div>

        <div class="deadline-field">
            <label>End</label>
            <p class="input-helper">End date.</p>
            <datepicker :datetime="deadline.deadline_time"></datepicker>
            <input type="hidden" :value="deadline.deadline_time.time">
        </div>

        <div class="deadline-field is-flex-1">
            <label>Teachers</label>

            <p class="input-helper">Teachers attending this lab session.</p>

            <multiselect v-model="deadline.teachers" :options="teachers" :multiple="true" label="name"
                         :close-on-select="false" placeholder="Select teachers" trackBy="name"
                         :clear-on-select="true" style="width: 300px">
            </multiselect>

        </div>

    </div>
</template>

<script>
    import { Datepicker } from '../../../../../components/partials';
    import { Translate } from '../../../../../mixins';
    import { CharonSelect } from '../../../../../components/form';
    import Multiselect from 'vue-multiselect';

    export default {
        mixins: [ Translate ],

        components: { Datepicker, CharonSelect, Multiselect },

        props: {
            deadline: { required: true },
            teachers: { required: true },
        }
    }
</script>

<style src="../../../../../../../../../node_modules/vue-multiselect/dist/vue-multiselect.min.css"></style>
<style>
    /**
 * CONTENTS
 *
 * IMPORTS
 *
 * LAYOUT
 *
 * FORM_FIELDS
 * Labels
 * Sections
 * Grades Select
 * Grademap Tabs
 *
 * ACCESSORIES
 * Utility Classes
 * Toggle
 * Notification
 */

    /*---------------------------------*\
      #IMPORTS
    \*---------------------------------*/

    /*---------------------------------*\
      #FORM_FIELDS
    \*---------------------------------*/

    /*-----------------------*\
      #LABELS
    \*-----------------------*/

    label.required:after {
        content: ' *';
        color: red;
    }

    /*-----------------------*\
      #HELPER_TEXT
    \*-----------------------*/

    .fitem .input-helper {
        padding-left: 15px;
        padding-right: 15px;
    }

    .input-helper {
        font-size: 15px;
        color: #5e6977;
    }

    .deadline-row:not(:first-child) .deadline-field label {
        display: none;
    }

    /*-----------------------*\
      #SECTIONS
    \*-----------------------*/

    .section-title-right {
        float: right;
        display: -ms-flexbox;
        display: flex;
    }

    .section-title-right .toggle-container {
        margin-left: 20px;
    }

    .section-title-right__advanced-text {
        font-size: 0.8em;
    }

    fieldset legend {
        padding-top: 20px;
    }

    /*-----------------------*\
      #GRADES_SELECT
    \*-----------------------*/

    .grades-select-container {
        display: -ms-flexbox;
        display: flex;
    }

    .grades-select-col {
        padding: 0 10px;
    }

    .grades-select-col .checkbox input[type='checkbox'] {
        position: static;
        margin-left: 0;
    }

    /*-----------------------*\
      #GRADEMAP_TABS
    \*-----------------------*/

    .tabs {
        margin-top: 10px;
        border-bottom: 5px solid #0081D5;
    }

    .tabs ul {
        list-style: none;
        display: -ms-flexbox;
        display: flex;
        padding: 0;
        margin: 0;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
    }

    .tabs .tab {
        -ms-flex-positive: 1;
        flex-grow: 1;
        margin: 0 5px;
        text-align: center;
        cursor: pointer;
        background-color: #ECEFF4;
    }

    .tabs .tab:last-child {
        margin-right: 0;
    }

    .tabs .tab:first-child {
        margin-left: 0;
    }

    .tabs .tab a {
        display: inline-block;
        padding: 5px;
        width: 100%;
        color: #777777;
    }

    .tabs .tab a:hover,
    .tabs .tab a:focus {
        text-decoration: none;
    }

    .tabs .tab.is-active {
        background-color: #0081D5;
    }

    .tabs .tab.is-active a {
        color: #ffffff;
    }

    .tab-details {
        border-bottom: 5px solid #0081D5;
        margin-bottom: 35px;
        padding-bottom: 10px;
    }

    .grademap-row {
        display: -ms-flexbox;
        display: flex;
        padding-top: 10px;
        padding-bottom: 10px;
    }

    .grademap-row .grademap-field {
        width: 100%;
        padding: 0 15px;
    }

    .grademap-row .grademap-field .input-helper {
        padding-left: 0;
        padding-right: 0;
    }

    /*-----------------------*\
      #DEADLINES
    \*-----------------------*/

    .deadline-row {
        -ms-flex-align: end;
        align-items: flex-end;
    }

    .deadline-row:not(:first-child) .deadline-field .input-helper {
        visibility: hidden;
        height: 0;
        margin: 0;
    }

    .deadline-field {
        padding: 10px 15px;
    }

    .deadline-field .input-helper {
        padding-left: 0;
        padding-right: 0;
    }

    .add-deadline-btn {
        height: 46px;
        margin: 10px 15px;
    }

    .remove-deadline-btn {
        height: 46px;
        min-width: 100%;
        margin: 0;
    }

    /*-----------------------*\
      #DEFENSE
    \*-----------------------*/

    .defense-row {
        -ms-flex-align: end;
        align-items: flex-end;
    }

    .defense-row:not(:first-child) .defense-field .input-helper {
        visibility: hidden;
        height: 0;
        margin: 0;
    }

    .defense-field {
        padding: 10px 15px;
    }

    .defense-field .input-helper {
        padding-left: 0;
        padding-right: 0;
    }

    .defense-field .checkbox-text {
        display: inline;
        margin-left: 4px;
        color: #5e6977 !important;
    }

    .defense-field .multiselect__width {
        max-width: 275px;
    }

    /*---------------------------------*\
      #ACCESSORIES
    \*---------------------------------*/

    /*-----------------------*\
      #TOGGLE
    \*-----------------------*/

    .is-flex {
        display: -ms-flexbox;
        display: flex;
    }

    .is-flex-1 {
        -ms-flex-positive: 1;
        flex-grow: 1;
    }

    /*-----------------------*\
      #TOGGLE
    \*-----------------------*/

    .toggle {
        display: none;
    }

    .toggle + .toggle-btn {
        outline: 0;
        display: block;
        width: 2.6em;
        height: 1.3em;
        position: relative;
        cursor: pointer;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    .toggle + .toggle-btn:after,
    .toggle + .toggle-btn:before {
        position: relative;
        display: block;
        content: "";
        width: 50%;
        height: 100%;
    }

    .toggle + .toggle-btn:after {
        left: 0;
    }

    .toggle + .toggle-btn:before {
        display: none;
    }

    .toggle:checked + .toggle-btn:after {
        left: 50%;
    }

    .toggle-btn {
        margin: 2px;
    }

    .toggle-light + .toggle-btn {
        background: #f0f0f0;
        border-radius: 2em;
        padding: 2px;
        transition: all .4s ease;
    }

    .toggle-light + .toggle-btn:after {
        border-radius: 50%;
        background: #ffffff;
        transition: all .2s ease;
    }

    .toggle-light:checked + .toggle-btn {
        background: #9fd6ae;
    }

    /*-----------------------*\
      #NOTIFICATION
    \*-----------------------*/

    .notification {
        position: relative;
        display: -ms-flexbox;
        display: flex;
        background-color: whitesmoke;
        border-radius: 3px;
        padding: 1.25rem 2.5rem 1.25rem 1.5rem;
    }

    .notification.is-danger {
        background-color: #ff3860;
        color: #fff;
    }

    .notification.is-danger a {
        color: #fff;
        text-decoration: underline;
    }

    .notification > .delete {
        position: absolute;
        right: 0.5em;
        top: 0.5em;
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        -moz-appearance: none;
        -webkit-appearance: none;
        background-color: rgba(10, 10, 10, 0.2);
        border: none;
        border-radius: 290486px;
        cursor: pointer;
        display: inline-block;
        -ms-flex-positive: 0;
        flex-grow: 0;
        -ms-flex-negative: 0;
        flex-shrink: 0;
        font-size: 1rem;
        height: 20px;
        max-height: 20px;
        max-width: 20px;
        min-height: 20px;
        min-width: 20px;
        outline: none;
        position: relative;
        vertical-align: top;
        width: 20px;
    }

    .notification > .delete:before,
    .notification > .delete:after {
        background-color: white;
        content: "";
        display: block;
        left: 50%;
        position: absolute;
        top: 50%;
        transform: translateX(-50%) translateY(-50%) rotate(45deg);
        transform-origin: center center;
    }

    .notification > .delete:before {
        height: 2px;
        width: 50%;
    }

    .notification > .delete:after {
        height: 50%;
        width: 2px;
    }

    .slide-fade-enter-active {
        transition: all .3s ease;
    }

    .slide-fade-leave-active {
        transition: all 0.5s cubic-bezier(1, 0.5, 0.8, 1);
    }

    .slide-fade-enter,
    .slide-fade-leave-to {
        transform: translateY(10px);
        opacity: 0;
    }

    .popup-notification {
        position: fixed;
        bottom: 30px;
        z-index: 2;
        width: 100%;
        -ms-flex-pack: center;
        justify-content: center;
        left: 0;
    }

    /*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbInBsdWdpbi9yZXNvdXJjZXMvYXNzZXRzL3Nhc3MvaW5zdGFuY2VGb3JtLnNjc3MiLCJwbHVnaW4vcmVzb3VyY2VzL2Fzc2V0cy9zYXNzL192YXJpYWJsZXMuc2NzcyJdLCJzb3VyY2VzQ29udGVudCI6WyIvKipcclxuICogQ09OVEVOVFNcclxuICpcclxuICogSU1QT1JUU1xyXG4gKlxyXG4gKiBMQVlPVVRcclxuICpcclxuICogRk9STV9GSUVMRFNcclxuICogTGFiZWxzXHJcbiAqIFNlY3Rpb25zXHJcbiAqIEdyYWRlcyBTZWxlY3RcclxuICogR3JhZGVtYXAgVGFic1xyXG4gKlxyXG4gKiBBQ0NFU1NPUklFU1xyXG4gKiBVdGlsaXR5IENsYXNzZXNcclxuICogVG9nZ2xlXHJcbiAqIE5vdGlmaWNhdGlvblxyXG4gKi9cclxuLyotLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0qXFxcclxuICAjSU1QT1JUU1xyXG5cXCotLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0qL1xyXG5AaW1wb3J0ICdfdmFyaWFibGVzLnNjc3MnO1xyXG5cclxuXHJcbi8qLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tKlxcXHJcbiAgI0ZPUk1fRklFTERTXHJcblxcKi0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLSovXHJcblxyXG4vKi0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tKlxcXHJcbiAgI0xBQkVMU1xyXG5cXCotLS0tLS0tLS0tLS0tLS0tLS0tLS0tLSovXHJcblxyXG5sYWJlbC5yZXF1aXJlZDphZnRlciB7XHJcbiAgY29udGVudDogJyAqJztcclxuICBjb2xvcjogcmVkO1xyXG59XHJcblxyXG5cclxuLyotLS0tLS0tLS0tLS0tLS0tLS0tLS0tLSpcXFxyXG4gICNIRUxQRVJfVEVYVFxyXG5cXCotLS0tLS0tLS0tLS0tLS0tLS0tLS0tLSovXHJcblxyXG4uZml0ZW0gLmlucHV0LWhlbHBlciB7XHJcbiAgcGFkZGluZy1sZWZ0OiAxNXB4O1xyXG4gIHBhZGRpbmctcmlnaHQ6IDE1cHg7XHJcbn1cclxuXHJcbi5pbnB1dC1oZWxwZXIge1xyXG4gIGZvbnQtc2l6ZTogMTVweDtcclxuICBjb2xvcjogIzVlNjk3NztcclxufVxyXG5cclxuLmRlYWRsaW5lLXJvdzpub3QoOmZpcnN0LWNoaWxkKSAuZGVhZGxpbmUtZmllbGQgbGFiZWwge1xyXG4gIGRpc3BsYXk6IG5vbmU7XHJcbn1cclxuXHJcblxyXG4vKi0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tKlxcXHJcbiAgI1NFQ1RJT05TXHJcblxcKi0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tKi9cclxuXHJcbi5zZWN0aW9uLXRpdGxlLXJpZ2h0IHtcclxuICBmbG9hdDogcmlnaHQ7XHJcbiAgZGlzcGxheTogZmxleDtcclxuXHJcbiAgLnRvZ2dsZS1jb250YWluZXIge1xyXG4gICAgbWFyZ2luLWxlZnQ6IDIwcHg7XHJcbiAgfVxyXG59XHJcblxyXG5cclxuLnNlY3Rpb24tdGl0bGUtcmlnaHRfX2FkdmFuY2VkLXRleHQge1xyXG4gIGZvbnQtc2l6ZTogMC44ZW07XHJcbn1cclxuXHJcbmZpZWxkc2V0IGxlZ2VuZCB7XHJcbiAgcGFkZGluZy10b3A6IDIwcHg7XHJcbn1cclxuXHJcblxyXG4vKi0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tKlxcXHJcbiAgI0dSQURFU19TRUxFQ1RcclxuXFwqLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0qL1xyXG5cclxuLmdyYWRlcy1zZWxlY3QtY29udGFpbmVyIHtcclxuICBkaXNwbGF5OiBmbGV4O1xyXG59XHJcblxyXG5cclxuLmdyYWRlcy1zZWxlY3QtY29sIHtcclxuICBwYWRkaW5nOiAwIDEwcHg7XHJcblxyXG4gIC5jaGVja2JveCBpbnB1dFt0eXBlPSdjaGVja2JveCddIHtcclxuICAgIHBvc2l0aW9uOiBzdGF0aWM7XHJcbiAgICBtYXJnaW4tbGVmdDogMDtcclxuICB9XHJcbn1cclxuXHJcblxyXG4vKi0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tKlxcXHJcbiAgI0dSQURFTUFQX1RBQlNcclxuXFwqLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0qL1xyXG5cclxuLnRhYnMge1xyXG4gIG1hcmdpbi10b3A6IDEwcHg7XHJcbiAgYm9yZGVyLWJvdHRvbTogNXB4IHNvbGlkICRicmFuZC1wcmltYXJ5O1xyXG5cclxuICB1bCB7XHJcbiAgICBsaXN0LXN0eWxlOiBub25lO1xyXG4gICAgZGlzcGxheTogZmxleDtcclxuICAgIHBhZGRpbmc6IDA7XHJcbiAgICBtYXJnaW46IDA7XHJcbiAgICBmbGV4LXdyYXA6IHdyYXA7XHJcbiAgfVxyXG59XHJcblxyXG5cclxuLnRhYnMgLnRhYiB7XHJcbiAgZmxleC1ncm93OiAxO1xyXG4gIG1hcmdpbjogMCA1cHg7XHJcbiAgdGV4dC1hbGlnbjogY2VudGVyO1xyXG5cclxuICBjdXJzb3I6IHBvaW50ZXI7XHJcbiAgYmFja2dyb3VuZC1jb2xvcjogJGJhY2tncm91bmQtZ3JleTtcclxuXHJcbiAgJjpsYXN0LWNoaWxkIHtcclxuICAgIG1hcmdpbi1yaWdodDogMDtcclxuICB9XHJcblxyXG4gICY6Zmlyc3QtY2hpbGQge1xyXG4gICAgbWFyZ2luLWxlZnQ6IDA7XHJcbiAgfVxyXG5cclxuICBhIHtcclxuICAgIGRpc3BsYXk6IGlubGluZS1ibG9jaztcclxuICAgIHBhZGRpbmc6IDVweDtcclxuICAgIHdpZHRoOiAxMDAlO1xyXG4gICAgY29sb3I6ICRkYXJrLXRleHQtY29sb3I7XHJcblxyXG4gICAgJjpob3ZlcixcclxuICAgICY6Zm9jdXMge1xyXG4gICAgICB0ZXh0LWRlY29yYXRpb246IG5vbmU7XHJcbiAgICB9XHJcbiAgfVxyXG5cclxuICAmLmlzLWFjdGl2ZSB7XHJcbiAgICBiYWNrZ3JvdW5kLWNvbG9yOiAkYnJhbmQtcHJpbWFyeTtcclxuXHJcbiAgICBhIHtcclxuICAgICAgY29sb3I6ICNmZmZmZmY7XHJcbiAgICB9XHJcbiAgfVxyXG59XHJcblxyXG4udGFiLWRldGFpbHMge1xyXG4gIGJvcmRlci1ib3R0b206IDVweCBzb2xpZCAkYnJhbmQtcHJpbWFyeTtcclxuICBtYXJnaW4tYm90dG9tOiAzNXB4O1xyXG4gIHBhZGRpbmctYm90dG9tOiAxMHB4O1xyXG59XHJcblxyXG4uZ3JhZGVtYXAtcm93IHtcclxuICBkaXNwbGF5OiBmbGV4O1xyXG4gIHBhZGRpbmctdG9wOiAxMHB4O1xyXG4gIHBhZGRpbmctYm90dG9tOiAxMHB4O1xyXG5cclxuICAuZ3JhZGVtYXAtZmllbGQge1xyXG4gICAgd2lkdGg6IDEwMCU7XHJcbiAgICBwYWRkaW5nOiAwIDE1cHg7XHJcblxyXG4gICAgLmlucHV0LWhlbHBlciB7XHJcbiAgICAgIHBhZGRpbmctbGVmdDogMDtcclxuICAgICAgcGFkZGluZy1yaWdodDogMDtcclxuICAgIH1cclxuICB9XHJcbn1cclxuXHJcblxyXG4vKi0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tKlxcXHJcbiAgI0RFQURMSU5FU1xyXG5cXCotLS0tLS0tLS0tLS0tLS0tLS0tLS0tLSovXHJcblxyXG4uZGVhZGxpbmUtcm93IHtcclxuICBhbGlnbi1pdGVtczogZmxleC1lbmQ7XHJcblxyXG4gICY6bm90KDpmaXJzdC1jaGlsZCkgLmRlYWRsaW5lLWZpZWxkIHtcclxuICAgIC5pbnB1dC1oZWxwZXIge1xyXG4gICAgICB2aXNpYmlsaXR5OiBoaWRkZW47XHJcbiAgICAgIGhlaWdodDogMDtcclxuICAgICAgbWFyZ2luOiAwO1xyXG4gICAgfVxyXG4gIH1cclxufVxyXG5cclxuLmRlYWRsaW5lLWZpZWxkIHtcclxuICBwYWRkaW5nOiAxMHB4IDE1cHg7XHJcblxyXG4gIC5pbnB1dC1oZWxwZXIge1xyXG4gICAgcGFkZGluZy1sZWZ0OiAwO1xyXG4gICAgcGFkZGluZy1yaWdodDogMDtcclxuICB9XHJcbn1cclxuXHJcbi5hZGQtZGVhZGxpbmUtYnRuIHtcclxuICBoZWlnaHQ6IDQ2cHg7XHJcbiAgbWFyZ2luOiAxMHB4IDE1cHg7XHJcbn1cclxuXHJcbi5yZW1vdmUtZGVhZGxpbmUtYnRuIHtcclxuICBoZWlnaHQ6IDQ2cHg7XHJcbiAgbWluLXdpZHRoOiAxMDAlO1xyXG4gIG1hcmdpbjogMDtcclxufVxyXG5cclxuLyotLS0tLS0tLS0tLS0tLS0tLS0tLS0tLSpcXFxyXG4gICNERUZFTlNFXHJcblxcKi0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tKi9cclxuXHJcbi5kZWZlbnNlLXJvdyB7XHJcbiAgYWxpZ24taXRlbXM6IGZsZXgtZW5kO1xyXG5cclxuICAmOm5vdCg6Zmlyc3QtY2hpbGQpIC5kZWZlbnNlLWZpZWxkIHtcclxuICAgIC5pbnB1dC1oZWxwZXIge1xyXG4gICAgICB2aXNpYmlsaXR5OiBoaWRkZW47XHJcbiAgICAgIGhlaWdodDogMDtcclxuICAgICAgbWFyZ2luOiAwO1xyXG4gICAgfVxyXG4gIH1cclxufVxyXG5cclxuLmRlZmVuc2UtZmllbGQge1xyXG4gIHBhZGRpbmc6IDEwcHggMTVweDtcclxuXHJcbiAgLmlucHV0LWhlbHBlciB7XHJcbiAgICBwYWRkaW5nLWxlZnQ6IDA7XHJcbiAgICBwYWRkaW5nLXJpZ2h0OiAwO1xyXG4gIH1cclxuXHJcbiAgLmNoZWNrYm94LXRleHQge1xyXG4gICAgZGlzcGxheTogaW5saW5lO1xyXG4gICAgbWFyZ2luLWxlZnQ6IDRweDtcclxuICAgIGNvbG9yOiAjNWU2OTc3ICFpbXBvcnRhbnQ7XHJcbiAgfVxyXG5cclxuICAubXVsdGlzZWxlY3RfX3dpZHRoIHtcclxuICAgIG1heC13aWR0aDogMjc1cHg7XHJcbiAgfVxyXG59XHJcblxyXG4vKi0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLSpcXFxyXG4gICNBQ0NFU1NPUklFU1xyXG5cXCotLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0qL1xyXG5cclxuXHJcbi8qLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0qXFxcclxuICAjVE9HR0xFXHJcblxcKi0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tKi9cclxuXHJcbi5pcy1mbGV4IHtcclxuICBkaXNwbGF5OiBmbGV4O1xyXG59XHJcblxyXG4uaXMtZmxleC0xIHtcclxuICBmbGV4LWdyb3c6IDE7XHJcbn1cclxuXHJcblxyXG4vKi0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tKlxcXHJcbiAgI1RPR0dMRVxyXG5cXCotLS0tLS0tLS0tLS0tLS0tLS0tLS0tLSovXHJcblxyXG4udG9nZ2xlIHtcclxuICBkaXNwbGF5OiBub25lO1xyXG5cclxuICArIC50b2dnbGUtYnRuIHtcclxuICAgIG91dGxpbmU6IDA7XHJcbiAgICBkaXNwbGF5OiBibG9jaztcclxuICAgIHdpZHRoOiAyLjZlbTtcclxuICAgIGhlaWdodDogMS4zZW07XHJcbiAgICBwb3NpdGlvbjogcmVsYXRpdmU7XHJcbiAgICBjdXJzb3I6IHBvaW50ZXI7XHJcbiAgICB1c2VyLXNlbGVjdDogbm9uZTtcclxuICAgICY6YWZ0ZXIsXHJcbiAgICAmOmJlZm9yZSB7XHJcbiAgICAgIHBvc2l0aW9uOiByZWxhdGl2ZTtcclxuICAgICAgZGlzcGxheTogYmxvY2s7XHJcbiAgICAgIGNvbnRlbnQ6IFwiXCI7XHJcbiAgICAgIHdpZHRoOiA1MCU7XHJcbiAgICAgIGhlaWdodDogMTAwJTtcclxuICAgIH1cclxuXHJcbiAgICAmOmFmdGVyIHtcclxuICAgICAgbGVmdDogMDtcclxuICAgIH1cclxuXHJcbiAgICAmOmJlZm9yZSB7XHJcbiAgICAgIGRpc3BsYXk6IG5vbmU7XHJcbiAgICB9XHJcbiAgfVxyXG5cclxuICAmOmNoZWNrZWQgKyAudG9nZ2xlLWJ0bjphZnRlciB7XHJcbiAgICBsZWZ0OiA1MCU7XHJcbiAgfVxyXG59XHJcblxyXG5cclxuLnRvZ2dsZS1idG4ge1xyXG4gIG1hcmdpbjogMnB4O1xyXG59XHJcblxyXG5cclxuLy8gVGhlbWVcclxuLnRvZ2dsZS1saWdodCB7XHJcbiAgKyAudG9nZ2xlLWJ0biB7XHJcbiAgICBiYWNrZ3JvdW5kOiAjZjBmMGYwO1xyXG4gICAgYm9yZGVyLXJhZGl1czogMmVtO1xyXG4gICAgcGFkZGluZzogMnB4O1xyXG4gICAgdHJhbnNpdGlvbjogYWxsIC40cyBlYXNlO1xyXG4gICAgJjphZnRlciB7XHJcbiAgICAgIGJvcmRlci1yYWRpdXM6IDUwJTtcclxuICAgICAgYmFja2dyb3VuZDogI2ZmZmZmZjtcclxuICAgICAgdHJhbnNpdGlvbjogYWxsIC4ycyBlYXNlO1xyXG4gICAgfVxyXG4gIH1cclxuXHJcbiAgJjpjaGVja2VkICsgLnRvZ2dsZS1idG4ge1xyXG4gICAgYmFja2dyb3VuZDogIzlmZDZhZTtcclxuICB9XHJcbn1cclxuXHJcblxyXG4vKi0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tKlxcXHJcbiAgI05PVElGSUNBVElPTlxyXG5cXCotLS0tLS0tLS0tLS0tLS0tLS0tLS0tLSovXHJcblxyXG4ubm90aWZpY2F0aW9uIHtcclxuICBwb3NpdGlvbjogcmVsYXRpdmU7XHJcbiAgZGlzcGxheTogZmxleDtcclxuICBiYWNrZ3JvdW5kLWNvbG9yOiB3aGl0ZXNtb2tlO1xyXG4gIGJvcmRlci1yYWRpdXM6IDNweDtcclxuICBwYWRkaW5nOiAxLjI1cmVtIDIuNXJlbSAxLjI1cmVtIDEuNXJlbTtcclxuXHJcbiAgJi5pcy1kYW5nZXIge1xyXG4gICAgYmFja2dyb3VuZC1jb2xvcjogJGRhbmdlcjtcclxuICAgIGNvbG9yOiAkZGFuZ2VyLWludmVydDtcclxuXHJcbiAgICBhIHtcclxuICAgICAgY29sb3I6ICRkYW5nZXItaW52ZXJ0O1xyXG4gICAgICB0ZXh0LWRlY29yYXRpb246IHVuZGVybGluZTtcclxuICAgIH1cclxuICB9XHJcblxyXG4gID4gLmRlbGV0ZSB7XHJcbiAgICBwb3NpdGlvbjogYWJzb2x1dGU7XHJcbiAgICByaWdodDogMC41ZW07XHJcbiAgICB0b3A6IDAuNWVtO1xyXG5cclxuICAgIC13ZWJraXQtdG91Y2gtY2FsbG91dDogbm9uZTtcclxuICAgIC13ZWJraXQtdXNlci1zZWxlY3Q6IG5vbmU7XHJcbiAgICAtbW96LXVzZXItc2VsZWN0OiBub25lO1xyXG4gICAgLW1zLXVzZXItc2VsZWN0OiBub25lO1xyXG4gICAgdXNlci1zZWxlY3Q6IG5vbmU7XHJcbiAgICAtbW96LWFwcGVhcmFuY2U6IG5vbmU7XHJcbiAgICAtd2Via2l0LWFwcGVhcmFuY2U6IG5vbmU7XHJcbiAgICBiYWNrZ3JvdW5kLWNvbG9yOiByZ2JhKDEwLCAxMCwgMTAsIDAuMik7XHJcbiAgICBib3JkZXI6IG5vbmU7XHJcbiAgICBib3JkZXItcmFkaXVzOiAyOTA0ODZweDtcclxuICAgIGN1cnNvcjogcG9pbnRlcjtcclxuICAgIGRpc3BsYXk6IGlubGluZS1ibG9jaztcclxuICAgIC13ZWJraXQtYm94LWZsZXg6IDA7XHJcbiAgICAtbXMtZmxleC1wb3NpdGl2ZTogMDtcclxuICAgIGZsZXgtZ3JvdzogMDtcclxuICAgIC1tcy1mbGV4LW5lZ2F0aXZlOiAwO1xyXG4gICAgZmxleC1zaHJpbms6IDA7XHJcbiAgICBmb250LXNpemU6IDFyZW07XHJcbiAgICBoZWlnaHQ6IDIwcHg7XHJcbiAgICBtYXgtaGVpZ2h0OiAyMHB4O1xyXG4gICAgbWF4LXdpZHRoOiAyMHB4O1xyXG4gICAgbWluLWhlaWdodDogMjBweDtcclxuICAgIG1pbi13aWR0aDogMjBweDtcclxuICAgIG91dGxpbmU6IG5vbmU7XHJcbiAgICBwb3NpdGlvbjogcmVsYXRpdmU7XHJcbiAgICB2ZXJ0aWNhbC1hbGlnbjogdG9wO1xyXG4gICAgd2lkdGg6IDIwcHg7XHJcblxyXG4gICAgJjpiZWZvcmUsXHJcbiAgICAmOmFmdGVyIHtcclxuICAgICAgYmFja2dyb3VuZC1jb2xvcjogd2hpdGU7XHJcbiAgICAgIGNvbnRlbnQ6IFwiXCI7XHJcbiAgICAgIGRpc3BsYXk6IGJsb2NrO1xyXG4gICAgICBsZWZ0OiA1MCU7XHJcbiAgICAgIHBvc2l0aW9uOiBhYnNvbHV0ZTtcclxuICAgICAgdG9wOiA1MCU7XHJcbiAgICAgIC13ZWJraXQtdHJhbnNmb3JtOiB0cmFuc2xhdGVYKC01MCUpIHRyYW5zbGF0ZVkoLTUwJSkgcm90YXRlKDQ1ZGVnKTtcclxuICAgICAgdHJhbnNmb3JtOiB0cmFuc2xhdGVYKC01MCUpIHRyYW5zbGF0ZVkoLTUwJSkgcm90YXRlKDQ1ZGVnKTtcclxuICAgICAgLXdlYmtpdC10cmFuc2Zvcm0tb3JpZ2luOiBjZW50ZXIgY2VudGVyO1xyXG4gICAgICB0cmFuc2Zvcm0tb3JpZ2luOiBjZW50ZXIgY2VudGVyO1xyXG4gICAgfVxyXG5cclxuICAgICY6YmVmb3JlIHtcclxuICAgICAgaGVpZ2h0OiAycHg7XHJcbiAgICAgIHdpZHRoOiA1MCU7XHJcbiAgICB9XHJcblxyXG4gICAgJjphZnRlciB7XHJcbiAgICAgIGhlaWdodDogNTAlO1xyXG4gICAgICB3aWR0aDogMnB4O1xyXG4gICAgfVxyXG4gIH1cclxufVxyXG5cclxuLnNsaWRlLWZhZGUtZW50ZXItYWN0aXZlIHtcclxuICB0cmFuc2l0aW9uOiBhbGwgLjNzIGVhc2U7XHJcbn1cclxuXHJcbi5zbGlkZS1mYWRlLWxlYXZlLWFjdGl2ZSB7XHJcbiAgdHJhbnNpdGlvbjogYWxsIC41cyBjdWJpYy1iZXppZXIoMS4wLCAwLjUsIDAuOCwgMS4wKTtcclxufVxyXG5cclxuLnNsaWRlLWZhZGUtZW50ZXIsXHJcbi5zbGlkZS1mYWRlLWxlYXZlLXRvIHtcclxuICB0cmFuc2Zvcm06IHRyYW5zbGF0ZVkoMTBweCk7XHJcbiAgb3BhY2l0eTogMDtcclxufVxyXG5cclxuLnBvcHVwLW5vdGlmaWNhdGlvbiB7XHJcbiAgcG9zaXRpb246IGZpeGVkO1xyXG4gIGJvdHRvbTogMzBweDtcclxuICB6LWluZGV4OiAyO1xyXG4gIHdpZHRoOiAxMDAlO1xyXG4gIGp1c3RpZnktY29udGVudDogY2VudGVyO1xyXG4gIGxlZnQ6IDA7XHJcbn1cclxuIiwiJGJyYW5kLXByaW1hcnk6ICMwMDgxRDU7XG4kYmFja2dyb3VuZC1ncmV5OiAjRUNFRkY0O1xuJGRhbmdlcjogI2ZmMzg2MDtcbiRkYW5nZXItaW52ZXJ0OiAjZmZmO1xuXG4kZGFyay10ZXh0LWNvbG9yOiAjNzc3Nzc3O1xuIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7Ozs7Ozs7Ozs7OztHQWlCRztBQUNIOztxQ0FFcUM7QUFJckM7O3FDQUVxQztBQUVyQzs7MkJBRTJCO0FBRTNCLEFBQUEsS0FBSyxBQUFBLFNBQVMsQUFBQSxNQUFNLENBQUM7RUFDbkIsT0FBTyxFQUFFLElBQUk7RUFDYixLQUFLLEVBQUUsR0FBRztDQUNYOztBQUdEOzsyQkFFMkI7QUFFM0IsQUFBQSxNQUFNLENBQUMsYUFBYSxDQUFDO0VBQ25CLFlBQVksRUFBRSxJQUFJO0VBQ2xCLGFBQWEsRUFBRSxJQUFJO0NBQ3BCOztBQUVELEFBQUEsYUFBYSxDQUFDO0VBQ1osU0FBUyxFQUFFLElBQUk7RUFDZixLQUFLLEVBQUUsT0FBTztDQUNmOztBQUVELEFBQUEsYUFBYSxBQUFBLElBQUssQ0FBQSxZQUFZLEVBQUUsZUFBZSxDQUFDLEtBQUssQ0FBQztFQUNwRCxPQUFPLEVBQUUsSUFBSTtDQUNkOztBQUdEOzsyQkFFMkI7QUFFM0IsQUFBQSxvQkFBb0IsQ0FBQztFQUNuQixLQUFLLEVBQUUsS0FBSztFQUNaLE9BQU8sRUFBRSxJQUFJO0NBS2Q7O0FBUEQsQUFJRSxvQkFKa0IsQ0FJbEIsaUJBQWlCLENBQUM7RUFDaEIsV0FBVyxFQUFFLElBQUk7Q0FDbEI7O0FBSUgsQUFBQSxtQ0FBbUMsQ0FBQztFQUNsQyxTQUFTLEVBQUUsS0FBSztDQUNqQjs7QUFFRCxBQUFBLFFBQVEsQ0FBQyxNQUFNLENBQUM7RUFDZCxXQUFXLEVBQUUsSUFBSTtDQUNsQjs7QUFHRDs7MkJBRTJCO0FBRTNCLEFBQUEsd0JBQXdCLENBQUM7RUFDdkIsT0FBTyxFQUFFLElBQUk7Q0FDZDs7QUFHRCxBQUFBLGtCQUFrQixDQUFDO0VBQ2pCLE9BQU8sRUFBRSxNQUFNO0NBTWhCOztBQVBELEFBR0Usa0JBSGdCLENBR2hCLFNBQVMsQ0FBQyxLQUFLLENBQUEsQUFBQSxJQUFDLENBQUssVUFBVSxBQUFmLEVBQWlCO0VBQy9CLFFBQVEsRUFBRSxNQUFNO0VBQ2hCLFdBQVcsRUFBRSxDQUFDO0NBQ2Y7O0FBSUg7OzJCQUUyQjtBQUUzQixBQUFBLEtBQUssQ0FBQztFQUNKLFVBQVUsRUFBRSxJQUFJO0VBQ2hCLGFBQWEsRUFBRSxHQUFHLENBQUMsS0FBSyxDQ3pHVixPQUFPO0NEa0h0Qjs7QUFYRCxBQUlFLEtBSkcsQ0FJSCxFQUFFLENBQUM7RUFDRCxVQUFVLEVBQUUsSUFBSTtFQUNoQixPQUFPLEVBQUUsSUFBSTtFQUNiLE9BQU8sRUFBRSxDQUFDO0VBQ1YsTUFBTSxFQUFFLENBQUM7RUFDVCxTQUFTLEVBQUUsSUFBSTtDQUNoQjs7QUFJSCxBQUFBLEtBQUssQ0FBQyxJQUFJLENBQUM7RUFDVCxTQUFTLEVBQUUsQ0FBQztFQUNaLE1BQU0sRUFBRSxLQUFLO0VBQ2IsVUFBVSxFQUFFLE1BQU07RUFFbEIsTUFBTSxFQUFFLE9BQU87RUFDZixnQkFBZ0IsRUMxSEEsT0FBTztDRHVKeEI7O0FBbkNELEFBUUUsS0FSRyxDQUFDLElBQUksQUFRUCxXQUFXLENBQUM7RUFDWCxZQUFZLEVBQUUsQ0FBQztDQUNoQjs7QUFWSCxBQVlFLEtBWkcsQ0FBQyxJQUFJLEFBWVAsWUFBWSxDQUFDO0VBQ1osV0FBVyxFQUFFLENBQUM7Q0FDZjs7QUFkSCxBQWdCRSxLQWhCRyxDQUFDLElBQUksQ0FnQlIsQ0FBQyxDQUFDO0VBQ0EsT0FBTyxFQUFFLFlBQVk7RUFDckIsT0FBTyxFQUFFLEdBQUc7RUFDWixLQUFLLEVBQUUsSUFBSTtFQUNYLEtBQUssRUNwSVMsT0FBTztDRDBJdEI7O0FBMUJILEFBc0JJLEtBdEJDLENBQUMsSUFBSSxDQWdCUixDQUFDLEFBTUUsTUFBTSxFQXRCWCxLQUFLLENBQUMsSUFBSSxDQWdCUixDQUFDLEFBT0UsTUFBTSxDQUFDO0VBQ04sZUFBZSxFQUFFLElBQUk7Q0FDdEI7O0FBekJMLEFBNEJFLEtBNUJHLENBQUMsSUFBSSxBQTRCUCxVQUFVLENBQUM7RUFDVixnQkFBZ0IsRUNsSkosT0FBTztDRHVKcEI7O0FBbENILEFBK0JJLEtBL0JDLENBQUMsSUFBSSxBQTRCUCxVQUFVLENBR1QsQ0FBQyxDQUFDO0VBQ0EsS0FBSyxFQUFFLE9BQU87Q0FDZjs7QUFJTCxBQUFBLFlBQVksQ0FBQztFQUNYLGFBQWEsRUFBRSxHQUFHLENBQUMsS0FBSyxDQzNKVixPQUFPO0VENEpyQixhQUFhLEVBQUUsSUFBSTtFQUNuQixjQUFjLEVBQUUsSUFBSTtDQUNyQjs7QUFFRCxBQUFBLGFBQWEsQ0FBQztFQUNaLE9BQU8sRUFBRSxJQUFJO0VBQ2IsV0FBVyxFQUFFLElBQUk7RUFDakIsY0FBYyxFQUFFLElBQUk7Q0FXckI7O0FBZEQsQUFLRSxhQUxXLENBS1gsZUFBZSxDQUFDO0VBQ2QsS0FBSyxFQUFFLElBQUk7RUFDWCxPQUFPLEVBQUUsTUFBTTtDQU1oQjs7QUFiSCxBQVNJLGFBVFMsQ0FLWCxlQUFlLENBSWIsYUFBYSxDQUFDO0VBQ1osWUFBWSxFQUFFLENBQUM7RUFDZixhQUFhLEVBQUUsQ0FBQztDQUNqQjs7QUFLTDs7MkJBRTJCO0FBRTNCLEFBQUEsYUFBYSxDQUFDO0VBQ1osV0FBVyxFQUFFLFFBQVE7Q0FTdEI7O0FBVkQsQUFJSSxhQUpTLEFBR1YsSUFBSyxDQUFBLFlBQVksRUFBRSxlQUFlLENBQ2pDLGFBQWEsQ0FBQztFQUNaLFVBQVUsRUFBRSxNQUFNO0VBQ2xCLE1BQU0sRUFBRSxDQUFDO0VBQ1QsTUFBTSxFQUFFLENBQUM7Q0FDVjs7QUFJTCxBQUFBLGVBQWUsQ0FBQztFQUNkLE9BQU8sRUFBRSxTQUFTO0NBTW5COztBQVBELEFBR0UsZUFIYSxDQUdiLGFBQWEsQ0FBQztFQUNaLFlBQVksRUFBRSxDQUFDO0VBQ2YsYUFBYSxFQUFFLENBQUM7Q0FDakI7O0FBR0gsQUFBQSxpQkFBaUIsQ0FBQztFQUNoQixNQUFNLEVBQUUsSUFBSTtFQUNaLE1BQU0sRUFBRSxTQUFTO0NBQ2xCOztBQUVELEFBQUEsb0JBQW9CLENBQUM7RUFDbkIsTUFBTSxFQUFFLElBQUk7RUFDWixTQUFTLEVBQUUsSUFBSTtFQUNmLE1BQU0sRUFBRSxDQUFDO0NBQ1Y7O0FBRUQ7OzJCQUUyQjtBQUUzQixBQUFBLFlBQVksQ0FBQztFQUNYLFdBQVcsRUFBRSxRQUFRO0NBU3RCOztBQVZELEFBSUksWUFKUSxBQUdULElBQUssQ0FBQSxZQUFZLEVBQUUsY0FBYyxDQUNoQyxhQUFhLENBQUM7RUFDWixVQUFVLEVBQUUsTUFBTTtFQUNsQixNQUFNLEVBQUUsQ0FBQztFQUNULE1BQU0sRUFBRSxDQUFDO0NBQ1Y7O0FBSUwsQUFBQSxjQUFjLENBQUM7RUFDYixPQUFPLEVBQUUsU0FBUztDQWdCbkI7O0FBakJELEFBR0UsY0FIWSxDQUdaLGFBQWEsQ0FBQztFQUNaLFlBQVksRUFBRSxDQUFDO0VBQ2YsYUFBYSxFQUFFLENBQUM7Q0FDakI7O0FBTkgsQUFRRSxjQVJZLENBUVosY0FBYyxDQUFDO0VBQ2IsT0FBTyxFQUFFLE1BQU07RUFDZixXQUFXLEVBQUUsR0FBRztFQUNoQixLQUFLLEVBQUUsa0JBQWtCO0NBQzFCOztBQVpILEFBY0UsY0FkWSxDQWNaLG1CQUFtQixDQUFDO0VBQ2xCLFNBQVMsRUFBRSxLQUFLO0NBQ2pCOztBQUdIOztxQ0FFcUM7QUFHckM7OzJCQUUyQjtBQUUzQixBQUFBLFFBQVEsQ0FBQztFQUNQLE9BQU8sRUFBRSxJQUFJO0NBQ2Q7O0FBRUQsQUFBQSxVQUFVLENBQUM7RUFDVCxTQUFTLEVBQUUsQ0FBQztDQUNiOztBQUdEOzsyQkFFMkI7QUFFM0IsQUFBQSxPQUFPLENBQUM7RUFDTixPQUFPLEVBQUUsSUFBSTtDQStCZDs7QUFoQ0QsQUFHRSxPQUhLLEdBR0gsV0FBVyxDQUFDO0VBQ1osT0FBTyxFQUFFLENBQUM7RUFDVixPQUFPLEVBQUUsS0FBSztFQUNkLEtBQUssRUFBRSxLQUFLO0VBQ1osTUFBTSxFQUFFLEtBQUs7RUFDYixRQUFRLEVBQUUsUUFBUTtFQUNsQixNQUFNLEVBQUUsT0FBTztFQUNmLFdBQVcsRUFBRSxJQUFJO0NBaUJsQjs7QUEzQkgsQUFXSSxPQVhHLEdBR0gsV0FBVyxBQVFWLE1BQU0sRUFYWCxPQUFPLEdBR0gsV0FBVyxBQVNWLE9BQU8sQ0FBQztFQUNQLFFBQVEsRUFBRSxRQUFRO0VBQ2xCLE9BQU8sRUFBRSxLQUFLO0VBQ2QsT0FBTyxFQUFFLEVBQUU7RUFDWCxLQUFLLEVBQUUsR0FBRztFQUNWLE1BQU0sRUFBRSxJQUFJO0NBQ2I7O0FBbEJMLEFBb0JJLE9BcEJHLEdBR0gsV0FBVyxBQWlCVixNQUFNLENBQUM7RUFDTixJQUFJLEVBQUUsQ0FBQztDQUNSOztBQXRCTCxBQXdCSSxPQXhCRyxHQUdILFdBQVcsQUFxQlYsT0FBTyxDQUFDO0VBQ1AsT0FBTyxFQUFFLElBQUk7Q0FDZDs7QUExQkwsQUE2QkUsT0E3QkssQUE2QkosUUFBUSxHQUFHLFdBQVcsQUFBQSxNQUFNLENBQUM7RUFDNUIsSUFBSSxFQUFFLEdBQUc7Q0FDVjs7QUFJSCxBQUFBLFdBQVcsQ0FBQztFQUNWLE1BQU0sRUFBRSxHQUFHO0NBQ1o7O0FBSUQsQUFDRSxhQURXLEdBQ1QsV0FBVyxDQUFDO0VBQ1osVUFBVSxFQUFFLE9BQU87RUFDbkIsYUFBYSxFQUFFLEdBQUc7RUFDbEIsT0FBTyxFQUFFLEdBQUc7RUFDWixVQUFVLEVBQUUsWUFBWTtDQU16Qjs7QUFYSCxBQU1JLGFBTlMsR0FDVCxXQUFXLEFBS1YsTUFBTSxDQUFDO0VBQ04sYUFBYSxFQUFFLEdBQUc7RUFDbEIsVUFBVSxFQUFFLE9BQU87RUFDbkIsVUFBVSxFQUFFLFlBQVk7Q0FDekI7O0FBVkwsQUFhRSxhQWJXLEFBYVYsUUFBUSxHQUFHLFdBQVcsQ0FBQztFQUN0QixVQUFVLEVBQUUsT0FBTztDQUNwQjs7QUFJSDs7MkJBRTJCO0FBRTNCLEFBQUEsYUFBYSxDQUFDO0VBQ1osUUFBUSxFQUFFLFFBQVE7RUFDbEIsT0FBTyxFQUFFLElBQUk7RUFDYixnQkFBZ0IsRUFBRSxVQUFVO0VBQzVCLGFBQWEsRUFBRSxHQUFHO0VBQ2xCLE9BQU8sRUFBRSw2QkFBNkI7Q0FxRXZDOztBQTFFRCxBQU9FLGFBUFcsQUFPVixVQUFVLENBQUM7RUFDVixnQkFBZ0IsRUNwVlgsT0FBTztFRHFWWixLQUFLLEVDcFZPLElBQUk7Q0QwVmpCOztBQWZILEFBV0ksYUFYUyxBQU9WLFVBQVUsQ0FJVCxDQUFDLENBQUM7RUFDQSxLQUFLLEVDdlZLLElBQUk7RUR3VmQsZUFBZSxFQUFFLFNBQVM7Q0FDM0I7O0FBZEwsQUFpQkUsYUFqQlcsR0FpQlQsT0FBTyxDQUFDO0VBQ1IsUUFBUSxFQUFFLFFBQVE7RUFDbEIsS0FBSyxFQUFFLEtBQUs7RUFDWixHQUFHLEVBQUUsS0FBSztFQUVWLHFCQUFxQixFQUFFLElBQUk7RUFDM0IsbUJBQW1CLEVBQUUsSUFBSTtFQUN6QixnQkFBZ0IsRUFBRSxJQUFJO0VBQ3RCLGVBQWUsRUFBRSxJQUFJO0VBQ3JCLFdBQVcsRUFBRSxJQUFJO0VBQ2pCLGVBQWUsRUFBRSxJQUFJO0VBQ3JCLGtCQUFrQixFQUFFLElBQUk7RUFDeEIsZ0JBQWdCLEVBQUUscUJBQXFCO0VBQ3ZDLE1BQU0sRUFBRSxJQUFJO0VBQ1osYUFBYSxFQUFFLFFBQVE7RUFDdkIsTUFBTSxFQUFFLE9BQU87RUFDZixPQUFPLEVBQUUsWUFBWTtFQUNyQixnQkFBZ0IsRUFBRSxDQUFDO0VBQ25CLGlCQUFpQixFQUFFLENBQUM7RUFDcEIsU0FBUyxFQUFFLENBQUM7RUFDWixpQkFBaUIsRUFBRSxDQUFDO0VBQ3BCLFdBQVcsRUFBRSxDQUFDO0VBQ2QsU0FBUyxFQUFFLElBQUk7RUFDZixNQUFNLEVBQUUsSUFBSTtFQUNaLFVBQVUsRUFBRSxJQUFJO0VBQ2hCLFNBQVMsRUFBRSxJQUFJO0VBQ2YsVUFBVSxFQUFFLElBQUk7RUFDaEIsU0FBUyxFQUFFLElBQUk7RUFDZixPQUFPLEVBQUUsSUFBSTtFQUNiLFFBQVEsRUFBRSxRQUFRO0VBQ2xCLGNBQWMsRUFBRSxHQUFHO0VBQ25CLEtBQUssRUFBRSxJQUFJO0NBeUJaOztBQXpFSCxBQWtESSxhQWxEUyxHQWlCVCxPQUFPLEFBaUNOLE9BQU8sRUFsRFosYUFBYSxHQWlCVCxPQUFPLEFBa0NOLE1BQU0sQ0FBQztFQUNOLGdCQUFnQixFQUFFLEtBQUs7RUFDdkIsT0FBTyxFQUFFLEVBQUU7RUFDWCxPQUFPLEVBQUUsS0FBSztFQUNkLElBQUksRUFBRSxHQUFHO0VBQ1QsUUFBUSxFQUFFLFFBQVE7RUFDbEIsR0FBRyxFQUFFLEdBQUc7RUFDUixpQkFBaUIsRUFBRSxnQkFBZ0IsQ0FBQyxnQkFBZ0IsQ0FBQyxhQUFhO0VBQ2xFLFNBQVMsRUFBRSxnQkFBZ0IsQ0FBQyxnQkFBZ0IsQ0FBQyxhQUFhO0VBQzFELHdCQUF3QixFQUFFLGFBQWE7RUFDdkMsZ0JBQWdCLEVBQUUsYUFBYTtDQUNoQzs7QUE5REwsQUFnRUksYUFoRVMsR0FpQlQsT0FBTyxBQStDTixPQUFPLENBQUM7RUFDUCxNQUFNLEVBQUUsR0FBRztFQUNYLEtBQUssRUFBRSxHQUFHO0NBQ1g7O0FBbkVMLEFBcUVJLGFBckVTLEdBaUJULE9BQU8sQUFvRE4sTUFBTSxDQUFDO0VBQ04sTUFBTSxFQUFFLEdBQUc7RUFDWCxLQUFLLEVBQUUsR0FBRztDQUNYOztBQUlMLEFBQUEsd0JBQXdCLENBQUM7RUFDdkIsVUFBVSxFQUFFLFlBQVk7Q0FDekI7O0FBRUQsQUFBQSx3QkFBd0IsQ0FBQztFQUN2QixVQUFVLEVBQUUsR0FBRyxDQUFDLElBQUcsQ0FBQyw0QkFBZ0M7Q0FDckQ7O0FBRUQsQUFBQSxpQkFBaUI7QUFDakIsb0JBQW9CLENBQUM7RUFDbkIsU0FBUyxFQUFFLGdCQUFnQjtFQUMzQixPQUFPLEVBQUUsQ0FBQztDQUNYOztBQUVELEFBQUEsbUJBQW1CLENBQUM7RUFDbEIsUUFBUSxFQUFFLEtBQUs7RUFDZixNQUFNLEVBQUUsSUFBSTtFQUNaLE9BQU8sRUFBRSxDQUFDO0VBQ1YsS0FBSyxFQUFFLElBQUk7RUFDWCxlQUFlLEVBQUUsTUFBTTtFQUN2QixJQUFJLEVBQUUsQ0FBQztDQUNSIn0= */
</style>
