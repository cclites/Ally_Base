<template>
    <b-card title="Caregiver Anniversary">
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

        props: {
            users: {
                type: Array,
                required: true,
            },
        },

        data() {
            return {
                loading: false,
                items: this.users,
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
    }
</script>

<style scoped>
.filter {
    margin: 20px 0;
}
</style>

