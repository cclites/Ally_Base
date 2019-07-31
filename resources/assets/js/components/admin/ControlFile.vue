<template>

    <b-row style="flex-direction: column">

        <b-form @submit=" copyText " @reset=" onReset " v-if=" show " class="w-100 px-3">

            <b-row>
                <b-col
                    v-for=" ( field, index ) in formModel "
                    :key="  index "
                    :cols=" field.col_xs "
                    :sm="   field.col_sm "
                    :md="   field.col_md "
                    :lg="   field.col_lg "
                >

                    <b-form-group
                        v-if="         field.onForm "
                        :id="          'input-group-' + index "
                        :label="       field.fieldTitle + ':' "
                        :label-for="   'input-' + index "
                        :description=" field.description "
                    >

                        <b-form-input
                            :id="          'input-' + index "
                            v-model="      form[ field.fieldName ] "
                            :type="        field.fieldType "
                            :required="    field.required "
                            :placeholder=" field.placeholder "
                            :maxlength="   field.length "
                        ></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>

            <b-button type="submit" variant="primary">Copy</b-button>
            <b-button type="reset" variant="danger">Reset</b-button>
        </b-form>

        <b-col cols="12">

            <b-card class="mt-3" header="Form Data Result">

                <input disabled type="text" class="form-control m-0 py-3" id="formattedInformation" :value=" formattedInformation " />
            </b-card>
        </b-col>
    </b-row>
</template>

<script>

    // great reference to reading COBOL layouts, what I followed to make the formattedOutput
    // http://www.3480-3590-data-conversion.com/article-reading-cobol-layouts-1.html

    export default {

        data() {

            return {

                show      : true, // a bootstrap-vue thing, trick to reset the form
                form      : {},
                formModel : [

                    {
                        onForm      : true,
                        fieldTitle  : 'Control Total Indicator',
                        fieldName   : 'cti',
                        fieldType   : 'text',
                        baseValue   : '',
                        placeholder : 'Enter Value',
                        description : '',
                        length      : 2,
                        decimal     : null,
                        fillChar    : ' ',
                        justify     : 'left',
                        required    : true,
                        col_xs      : 12,
                        col_sm      : 6,
                        col_md      : 6,
                        col_lg      : 4
                    },
                    {
                        onForm      : true,
                        fieldTitle  : 'Customer Point',
                        fieldName   : 'cp',
                        fieldType   : 'text',
                        baseValue   : '',
                        placeholder : 'Enter Value',
                        description : '',
                        length      : 8,
                        decimal     : null,
                        fillChar    : ' ',
                        justify     : 'left',
                        required    : true,
                        col_xs      : 12,
                        col_sm      : 6,
                        col_md      : 6,
                        col_lg      : 4
                    },
                    {
                        onForm      : true,
                        fieldTitle  : 'Customer CA',
                        fieldName   : 'cc',
                        fieldType   : 'text',
                        baseValue   : '',
                        placeholder : 'Enter Value',
                        description : 'Optional, spaces if not used',
                        length      : 9,
                        decimal     : null,
                        fillChar    : ' ',
                        justify     : 'left',
                        required    : false,
                        col_xs      : 12,
                        col_sm      : 6,
                        col_md      : 6,
                        col_lg      : 4
                    },
                    {
                        onForm      : true,
                        fieldTitle  : 'Customer CA Type',
                        fieldName   : 'cctype',
                        fieldType   : 'text',
                        baseValue   : '',
                        placeholder : 'Enter Value',
                        description : 'Optional, spaces if not used',
                        length      : 6,
                        decimal     : null,
                        fillChar    : ' ',
                        justify     : 'left',
                        required    : false,
                        col_xs      : 12,
                        col_sm      : 6,
                        col_md      : 6,
                        col_lg      : 4
                    },
                    {
                        onForm      : true,
                        fieldTitle  : 'Entry/Addenda Count Indicator',
                        fieldName   : 'caci',
                        fieldType   : 'text',
                        baseValue   : '',
                        placeholder : 'E, A or N',
                        description : 'E = Entry count, A = Addenda count, N = not used',
                        length      : 1,
                        decimal     : null,
                        fillChar    : ' ',
                        justify     : 'left',
                        required    : true,
                        col_xs      : 12,
                        col_sm      : 6,
                        col_md      : 6,
                        col_lg      : 4
                    },
                    {
                        onForm      : true,
                        fieldTitle  : 'Entry/Addenda Count',
                        fieldName   : 'cac',
                        fieldType   : 'text',
                        baseValue   : '',
                        placeholder : 'Enter Value',
                        description : '',
                        length      : 8,
                        decimal     : null,
                        fillChar    : '0',
                        justify     : 'right',
                        required    : true,
                        col_xs      : 12,
                        col_sm      : 6,
                        col_md      : 6,
                        col_lg      : 4
                    },
                    {
                        onForm      : true,
                        fieldTitle  : 'Debit Amount',
                        fieldName   : 'da',
                        fieldType   : 'text',
                        baseValue   : '',
                        placeholder : 'Enter Value',
                        description : '',
                        length      : 13,
                        decimal     : 2,
                        fillChar    : '0',
                        justify     : 'right',
                        required    : true,
                        col_xs      : 12,
                        col_sm      : 6,
                        col_md      : 6,
                        col_lg      : 4
                    },
                    {
                        onForm      : true,
                        fieldTitle  : 'Credit Amount',
                        fieldName   : 'ca',
                        fieldType   : 'text',
                        baseValue   : '',
                        placeholder : 'Enter Value',
                        description : '',
                        length      : 13,
                        decimal     : 2,
                        fillChar    : '0',
                        justify     : 'right',
                        required    : true,
                        col_xs      : 12,
                        col_sm      : 6,
                        col_md      : 6,
                        col_lg      : 4
                    },
                    {
                        onForm      : true,
                        fieldTitle  : 'Hash',
                        fieldName   : 'hash',
                        fieldType   : 'text',
                        baseValue   : '',
                        placeholder : 'Enter Value',
                        description : 'Zero Filled',
                        length      : 9,
                        decimal     : null,
                        fillChar    : '0',
                        justify     : 'right',
                        required    : true,
                        col_xs      : 12,
                        col_sm      : 6,
                        col_md      : 6,
                        col_lg      : 4
                    },
                    {
                        onForm      : false,
                        fieldTitle  : 'Filler',
                        fieldName   : 'filler',
                        fieldType   : 'text',
                        baseValue   : '',
                        placeholder : 'Enter Value',
                        description : '',
                        length      : 24,
                        decimal     : null,
                        fillChar    : ' ',
                        justify     : 'right',
                        required    : true,
                        col_xs      : 12,
                        col_sm      : 6,
                        col_md      : 6,
                        col_lg      : 4
                    },
                ],
            }
        },
        methods: {

            copyText( evt ) {

                evt.preventDefault();
                let data = document.querySelector( '#formattedInformation' );
                data.removeAttribute( "disabled" );
                data.select();

                try {

                    var successful = document.execCommand( 'copy' );
                    var msg = successful ? 'successful' : 'unsuccessful';
                    alert( 'Testing code was copied ' + msg );
                } catch ( err ) {

                    alert( 'Oops, unable to copy' );
                }

                data.setAttribute( "disabled", "true" );
                window.getSelection().removeAllRanges(); // recommended cleanup step
            },
            onReset( evt ) {

                evt.preventDefault();

                // Reset our form values
                this.setForm();

                // Trick to reset/clear native browser form validation state
                this.show = false;
                this.$nextTick( () => {

                    this.show = true;
                });
            },
            setForm(){

                this.form = _.reduce( this.formModel, ( obj, param ) => {

                    obj[ param.fieldName ] = param.baseValue;
                    return obj;
                }, {} );
            }
        },
        computed: {

            formattedInformation(){

                let string = [];
                let formInput = '';
                let fillers;
                let finalResult;

                this.formModel.forEach( val => {

                    fillers     = '';
                    finalResult = '';

                    formInput = this.form[ val.fieldName ] ? this.form[ val.fieldName ] : '';
                    for( let i = 0; i < val.length - formInput.trim().length; i++ ){

                        fillers += String( val.fillChar );
                    }
                    finalResult = ( val.justify == 'left' ? formInput + fillers : fillers + formInput );

                    if( val.decimal ) finalResult = finalResult.slice( 0, val.length - val.decimal ) + "." + finalResult.slice( val.length - val.decimal );

                    string.push( finalResult );
                });

                return string.join( '' );
            }
        },
        mounted(){

            this.setForm();
        }
    }
</script>

<style scoped>

</style>
