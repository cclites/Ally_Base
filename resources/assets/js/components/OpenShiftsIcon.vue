<template>

    <li class="nav-item pr-2">

        <a class="nav-link text-muted text-muted position-relative h-100" style="width: 55px" id="openShiftsDropdown" href="/business/schedule/open-shifts" aria-haspopup="true" aria-expanded="false">

            <i class="notification-icon open-shifts-icon"></i>

            <span class="badge badge-danger badge-notifications" v-if="total > 0">{{ total }}</span>

            <b-tooltip target="openShiftsDropdown" placement="left" title="View requests to open shifts"></b-tooltip>
        </a>
    </li>
</template>

<script>
import { mapGetters } from 'vuex';

    export default {

        props : {

            business : {

                type : String,
                default : null
            }
        },
        data() {

            return {

                total: 0
            }
        },
        computed : {

            ...mapGetters({

                getBusiness : 'getBusiness'
            }),
            current_business(){

                return this.business ? JSON.parse( this.business ) : null;
            }
        },
        async mounted() {

            await this.fetchRequestsCount();
        },

        methods: {

            fetchRequestsCount(){

                let form = new Form({

                    business_id : this.current_business.id,
                    count       : true
                });

                form.get( '/business/schedule/requests' )
                    .then( ({ data }) => {

                        console.log( data );
                        this.total = data.count;
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
        top: 18px;
        left: 34px;
    }
    .mdi-message {
        font-size: 24px;
        margin-right: -7px;
        padding-left: 7px;
    }
</style>