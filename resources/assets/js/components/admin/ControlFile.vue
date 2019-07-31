<template>

    <b-row>

        <b-col cols="12">

            <b-form @submit=" onSubmit " @reset=" onReset " v-if=" show ">

                <b-col
                    v-for=" ( field, index ) in formModel "
                    :key=" index "
                    :cols=" field.col_xs "
                    :sm=" field.col_sm "
                    :md=" field.col_md "
                    :lg=" field.col_lg "
                >

                    <b-form-group
                        :id=" 'input-group-' + index "
                        :label=" field.fieldTitle + ':' "
                        :label-for=" 'input-' + index "
                        :description=" field.description "
                    >

                        <b-form-input
                            :id=" 'input-' + index "
                            v-model=" form[ field.fieldName ] "
                            :type=" field.fieldType "
                            :required=" field.required "
                            :placeholder=" field.placeholder "
                            :size=" field.length "
                        ></b-form-input>
                    </b-form-group>
                </b-col>

                <b-button type="submit" variant="primary">Submit</b-button>
                <b-button type="reset" variant="danger">Reset</b-button>
            </b-form>
        </b-col>

        <b-col cols="12">

            <b-card class="mt-3" header="Form Data Result">

                <pre class="m-0">{{ form }}</pre>
            </b-card>
        </b-col>
    </b-row>
</template>

<script>
    export default {

        data() {

            return {

                show      : true, // a bootstrap-vue thing, trick to reset the form
                form      : {},
                formModel : [

                    {
                        fieldTitle  : 'Control Total Indicator',
                        fieldName   : 'cti',
                        fieldType   : 'text',
                        baseValue   : '',
                        placeholder : 'Enter Value',
                        description : '',
                        length      : 2,
                        decimal     : 0,
                        fillChar    : ' ',
                        justify     : 'right',
                        required    : true,
                        col_xs      : 12,
                        col_sm      : 6,
                        col_md      : 4,
                        col_lg      : 3
                    },
                    {
                        fieldTitle  : 'Customer Point',
                        fieldName   : 'cp',
                        fieldType   : 'text',
                        baseValue   : '',
                        placeholder : 'Enter Value',
                        description : '',
                        length      : 8,
                        decimal     : 0,
                        fillChar    : ' ',
                        justify     : 'right',
                        required    : true,
                        col_xs      : 12,
                        col_sm      : 6,
                        col_md      : 4,
                        col_lg      : 3
                    },
                    {
                        fieldTitle  : 'Customer CA',
                        fieldName   : 'cc',
                        fieldType   : 'text',
                        baseValue   : '',
                        placeholder : 'Enter Value',
                        description : '',
                        length      : 9,
                        decimal     : 0,
                        fillChar    : ' ',
                        justify     : 'right',
                        required    : true,
                        col_xs      : 12,
                        col_sm      : 6,
                        col_md      : 4,
                        col_lg      : 3
                    },
                    {
                        fieldTitle  : 'Customer CA Type',
                        fieldName   : 'cctype',
                        fieldType   : 'text',
                        baseValue   : '',
                        placeholder : 'Enter Value',
                        description : '',
                        length      : 6,
                        decimal     : 0,
                        fillChar    : ' ',
                        justify     : 'right',
                        required    : true,
                        col_xs      : 12,
                        col_sm      : 6,
                        col_md      : 4,
                        col_lg      : 3
                    },
                    {
                        fieldTitle  : 'Entry/Addenda Count Indicator',
                        fieldName   : 'caci',
                        fieldType   : 'text',
                        baseValue   : '',
                        placeholder : 'Enter Value',
                        description : '',
                        length      : 1,
                        decimal     : 0,
                        fillChar    : ' ',
                        justify     : 'right',
                        required    : true,
                        col_xs      : 12,
                        col_sm      : 6,
                        col_md      : 4,
                        col_lg      : 3
                    },
                    {
                        fieldTitle  : 'Entry/Addenda Count',
                        fieldName   : 'cac',
                        fieldType   : 'text',
                        baseValue   : '',
                        placeholder : 'Enter Value',
                        description : '',
                        length      : 8,
                        decimal     : 0,
                        fillChar    : ' ',
                        justify     : 'right',
                        required    : true,
                        col_xs      : 12,
                        col_sm      : 6,
                        col_md      : 4,
                        col_lg      : 3
                    },
                    {
                        fieldTitle  : 'Debit Amount',
                        fieldName   : 'da',
                        fieldType   : 'number',
                        baseValue   : '',
                        placeholder : 'Enter Value',
                        description : '',
                        length      : 11,
                        decimal     : 2,
                        fillChar    : ' ',
                        justify     : 'right',
                        required    : true,
                        col_xs      : 12,
                        col_sm      : 6,
                        col_md      : 4,
                        col_lg      : 3
                    },
                    {
                        fieldTitle  : 'Credit Amount',
                        fieldName   : 'da',
                        fieldType   : 'number',
                        baseValue   : '',
                        placeholder : 'Enter Value',
                        description : '',
                        length      : 11,
                        decimal     : 2,
                        fillChar    : ' ',
                        justify     : 'right',
                        required    : true,
                        col_xs      : 12,
                        col_sm      : 6,
                        col_md      : 4,
                        col_lg      : 3
                    },
                    {
                        fieldTitle  : 'Hash',
                        fieldName   : 'hash',
                        fieldType   : 'number',
                        baseValue   : '',
                        placeholder : 'Enter Value',
                        description : '',
                        length      : 9,
                        decimal     : 0,
                        fillChar    : ' ',
                        justify     : 'right',
                        required    : true,
                        col_xs      : 12,
                        col_sm      : 6,
                        col_md      : 4,
                        col_lg      : 3
                    },
                    {
                        fieldTitle  : 'Filler',
                        fieldName   : 'filler',
                        fieldType   : 'text',
                        baseValue   : '',
                        placeholder : 'Enter Value',
                        description : '',
                        length      : 24,
                        decimal     : 0,
                        fillChar    : ' ',
                        justify     : 'right',
                        required    : true,
                        col_xs      : 12,
                        col_sm      : 6,
                        col_md      : 4,
                        col_lg      : 3
                    },
                ],
            }
        },
        methods: {

            onSubmit( evt ) {

                evt.preventDefault()
                alert( JSON.stringify( this.form ) )
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
        mounted(){

            this.setForm();
        }
    }
</script>

<style scoped>

</style>
