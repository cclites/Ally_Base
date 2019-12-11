<template>
    <b-card title="Caregiver Anniversary">

        <b-row class="my-3">

            <b-col class="d-flex justify-content-end">

                <b-button @click=" fetchItems() " variant="info">Generate Report</b-button>
            </b-col>
        </b-row>
        <loading-card v-show="loading" />
        <div v-show="! loading" class="table-responsive">
            <ally-table id="caregiver-anniversary" :columns="fields" :items="items" sort-by="nameLastFirst">
                <template slot="name" scope="data">
                    <a :href="`/business/${type}s/${data.item.id}`">{{ data.item.name }}</a>
                </template>
            </ally-table>
        </div>
    </b-card>
</template>

<script>
    import FormatsDates from '../../../mixins/FormatsDates';

    export default {

        mixins: [ FormatsDates ],

        data() {

            return {

                loading: false,
                items: [],
                fields: [
                    {
                        key: 'nameLastFirst',
                        label: 'Name',
                        sortable: true,
                        shouldShow: true,
                    },
                    {
                        key: 'created_at',
                        label: 'First date referred',
                        sortable: true,
                        shouldShow: true,
                        formatter: x => { return this.formatDateFromUTC(x) }
                    },
                ],
            };
        },

        methods: {

            fetchItems(){

                this.loading = true;
                const form = new Form();

                form.get( 'anniversary?json=1' )
                    .then( res => {

                        this.items = res.data;
                    })
                    .catch( err => {

                        console.error( err );
                    })
                    .finally( () => {

                        this.loading = false;
                    })
            }
        }
    }
</script>

<style scoped>
.filter {
    margin: 20px 0;
}
</style>

