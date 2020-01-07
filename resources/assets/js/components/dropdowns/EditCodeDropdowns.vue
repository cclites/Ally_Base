<template>

    <b-row>

        <b-col sm="6">

            <b-form-group label="Visit Edit Reason" label-for="visit-edit-reason">

                <b-form-select
                    :disabled=" false "
                    name="visit-edit-reason"
                    v-model=" chosenReason "
                    >

                    <option value="">--None--</option>
                    <option v-for=" ( item, index ) in visit_edit_reasons " :value=" item.code " :key=" index ">{{ item.code + ': ' + item.description }}</option>
                </b-form-select>
            </b-form-group>
        </b-col>
        <b-col sm="6">

            <b-form-group label="Visit Edit Action" label-for="visit-edit-action">

                <b-form-select
                    :disabled=" false "
                    name="visit-edit-action"
                    v-model=" chosenAction "
                >

                    <option value="">--None--</option>
                    <option v-for=" ( item, index ) in visit_edit_actions " :value=" item.code " :key=" index ">{{ item.code + ': ' + item.description }}</option>
                </b-form-select>
            </b-form-group>
        </b-col>
    </b-row>
</template>

<script>

    import { mapGetters } from 'vuex';

    export default {

        props: [

            'visit_edit_action',
            'visit_edit_reason',
            'updateAction',
            'updateReason'
        ],
        data(){

            return {

            }
        },
        computed: {

            ...mapGetters({

                visit_edit_reasons : 'claims/visitEditReasonCodes',
                visit_edit_actions : 'claims/visitEditActionCodes'
            }),
            chosenReason: {

                get: function(){

                    return this.visit_edit_reason;
                },
                set: function( newValue ){

                    if( newValue && newValue !== this.visit_edit_reason ) this.updateReason( newValue );
                }
            },
            chosenAction: {

                get(){

                    return this.visit_edit_action;
                },
                set( newValue ){

                    if( newValue && newValue !== this.visit_edit_action ) this.updateAction( newValue );
                }
            }
        },
        async mounted(){

            await this.$store.dispatch('claims/fetchVisitEditCodes' );
        }
    }
</script>

<style>

</style>