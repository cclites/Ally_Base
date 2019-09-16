<template>
    <b-card :title="`${type} Birthday`">
        <b-row class="filter">
            <b-form-checkbox v-model="showEmpty" :value="true" :unchecked-value="false">
                Show {{type}} without birthdays
            </b-form-checkbox>
        </b-row>
        <loading-card v-show="loading" />
        <div v-show="! loading" class="table-responsive">
            <ally-table id="user-birthday" :columns="fields" :items="items" sort-by="nameLastFirst">
                <template slot="name" scope="data">
                    <a :href="`/business/${type}s/${data.item.id}`">{{ data.item.nameLastFirst }}</a>
                </template>
                <template slot="date_of_birth" scope="data">
                    {{ data.item.formatted_date }}
                </template>
            </ally-table>
        </div>
    </b-card>
</template>

<script>
    import FormatsDates from '../../../mixins/FormatsDates';

    export default {
        mixins: [ FormatsDates ],

        props: {
            type: {
                type: String,
                required: true,
            },
        },

        mounted() {
            this.fetch();
        },

        data() {
            return {
                loading: false,
                showEmpty: true,
                data: [],
                fields: [
                    {
                        key: 'nameLastFirst',
                        label: 'Name',
                        sortable: true,
                        shouldShow: true,
                    },
                    {
                        key: 'date_of_birth',
                        label: 'Birthday',
                        sortable: true,
                        shouldShow: true,
                    },
                ],
            };
        },

        computed: {
            items() {
                return this.data.filter(user => {
                    if (this.showEmpty) {
                        return true;
                    }
                    return !!user.date_of_birth;
                }).map((item) => {
                    return {
                        ...item,
                        formatted_date: item.date_of_birth ? moment(item.date_of_birth).format('MM/DD/YYYY') : '-',
                        date_of_birth: moment(item.date_of_birth).format('MMDD'),
                    };
                });
            }
        },

        methods: {
            async fetch() {
                this.loading = true;

                try {
                    const {data} = await axios.get(`/business/reports/data/birthdays?type=${this.type}`);
                    this.data = data;
                    this.loading = false
                }catch (e) {
                    console.error(e);
                    this.loading = false;
                }
            },
        },
    }
</script>

<style scoped>
.filter {
    margin: 20px 0;
}
</style>

