<template>

    <li style="cursor: pointer">
        <a href="" @click.prevent=" toggleOpenShiftsModal " aria-expanded="false" style="position:relative">

            <i class="fa fa-hand-paper-o"></i><span class="hide-menu">Open Shifts</span>
            <span class="badge badge-danger badge-notifications" v-if=" total > 0">{{ total }}</span>
        </a>
    </li>
</template>

<script>
    import { mapGetters, mapActions } from 'vuex';

    export default {

        props : {

            business : {

                type : String,
                default : null
            },
            route : String
        },
        data() {

            return {

            }
        },
        computed : {

            ...mapGetters({

                total : 'openShifts/newShiftsCount'
            }),
            current_business(){

                return this.business ? JSON.parse( this.business ) : null;
            }
        },
        async mounted() {

            await this.fetchOpenShiftsCount();
        },

        methods: {

            ...mapActions({

                setShiftsAndRequests  : 'openShifts/setShiftsAndRequests',
                toggleOpenShiftsModal : 'openShifts/toggleOpenShiftsModal'
            }),
            async fetchOpenShiftsCount(){

                let form = new Form({

                    businesses : this.current_business.id,
                    count      : true,
                    json       : true
                });

                form.get( '/schedule/open-shifts' )
                    .then( ({ data }) => {

                        console.log( 'SIDE MENU: ', data );
                        this.setShiftsAndRequests( data );
                    })
                    .catch( e => {

                    })
            }
        }
    }
</script>

<style>
    .badge-notifications {
        position: absolute;
        top: 0px;
        left: 25px;
    }
</style>