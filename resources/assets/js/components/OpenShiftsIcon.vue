<template>

    <li class="nav-item pr-2">

        <a class="nav-link text-muted text-muted position-relative h-100" style="width: 40px" id="openShiftsDropdown" @click.prevent=" toggleOpenShiftsModal " aria-haspopup="true" aria-expanded="false">

            <i class="solid-open-shifts-icon-top-menu"></i>

            <span class="badge badge-danger badge-notifications" v-if=" total > 0">{{ total }}</span>

            <b-tooltip target="openShiftsDropdown" placement="left" title="View requests to open shifts"></b-tooltip>
        </a>
    </li>
</template>

<script>
    import { mapGetters, mapActions } from 'vuex';

    export default {

        props : {

        },
        data() {

            return {

            }
        },
        computed : {

            ...mapGetters({

                total     : 'openShiftRequests/count',
                debounced : 'openShiftRequests/debounced'
            })
        },
        async mounted() {

            if( !this.debounced ) await this.fetchRequestsCount();
        },

        methods: {

            ...mapActions({

                setCount              : 'openShiftRequests/setCount',
                debounce              : 'openShiftRequests/debounce',
                toggleOpenShiftsModal : 'openShifts/toggleOpenShiftsModal'
            }),
            async fetchRequestsCount(){

                this.debounce();
                let form = new Form({

                    count : true
                });

                form.get( '/business/schedule/requests' )
                    .then( ({ data }) => {

                        console.log( data );
                        this.setCount( data.count );
                    })
                    .catch( e => {

                    })
            }
        }
    }
</script>

<style scoped>

    a {

        cursor: pointer;
    }

    .badge-notifications {
        position: absolute;
        top: 18px;
        left: 34px;
    }
</style>