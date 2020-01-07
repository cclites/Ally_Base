<template>
    <b-card :title="`${typeTitle}s Contacts`">

        <b-row>

            <b-col class="d-flex justify-content-end">

                <b-button variant="info" @click=" fetch() ">

                    Generate Report
                </b-button>
            </b-col>
        </b-row>
        <loading-card v-show="loading" />

        <div v-show="! loading" class="table-responsive">
            <b-table :items="items"
                show-empty
                :fields="fields">
                <template slot="name" scope="data">
                    <a :href="`/business/${type}s/${data.item.id}`">{{ data.item.name }}</a>
                </template>>
                <template slot="numbers" scope="data">
                    <div v-for="item in data.item.numbers" :key="item.id">
                        {{ item.number }} ({{ item.type}})
                    </div>
                </template>>
                <template slot="address" scope="data">
                    <div v-if="data.item.address">
                        <div>{{ data.item.address.address1 }}</div>
                        <div v-if="data.item.address.address2">{{ data.item.address.address2 }}</div>
                        <div>{{ data.item.address.city }}, {{ data.item.address.state }} {{ data.item.address.zip }} {{ data.item.address.country }}</div>
                    </div>
                </template>>
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import FormatsDates from '../../../mixins/FormatsDates';

    export default {
        mixins: [ FormatsDates ],

        props: ['type'],

        data() {
            return {
                items: [],
                loading: false,
                fields: [
                    { key: 'name', label: _.startCase(this.type) },
                    { key: 'email' },
                    { key: 'numbers', label: 'Phone Numbers' },
                    { key: 'address', label: 'Home Address' },
                ],
            };
        },

        computed: {
            typeTitle() {
                return this.type == 'client' ? 'Client' : 'Caregiver';
            },
        },

        methods: {
            fetch() {
                this.loading = true;
                axios.get(`/business/reports/contacts?fetch=1&type=${this.type}`)
                    .then(response => {
                        this.items = _.sortBy(response.data, 'name');
                        this.loading = false;
                    })
                    .catch(error => {
                        this.loading = false;
                        console.error(error.response);
                });
            },
        }
    }
</script>
