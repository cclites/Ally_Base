<template>
    <div>
        <div class="table-responsive">
            <b-table 
                bordered striped hover show-empty
                :items="items"
                :fields="columns"
                :current-page="currentPage"
                :per-page="perPage"
                :sort-by.sync="sortBy"
                @filtered="onFiltered"
            >
                <template v-for="field in columns" :slot="field.key || field" scope="data">
                    <slot v-bind="data" :name="field.key || field"> {{ data.item[field.key || field] }}</slot>
                </template>
            </b-table>
        </div>

        <b-row>
            <b-col lg="6">
                <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage"/>
            </b-col>
            <b-col lg="6" class="text-right">
                Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
            </b-col>
        </b-row>
    </div>
</template>

<script>
export default {
    props: {
        sortBy: {
            type: String,
            default: 'created_at',
        },
        columns: {
            type: Array,
            required: true,
        },
        items: {
            type: Array,
            required: true,
        },
    },

    data() {
        return {
            totalRows: 0,
            perPage: 15,
            currentPage: 1,
        };
    },

    methods: {
      onFiltered(filteredItems) {
            // Trigger pagination to update the number of buttons/pages due to filtering
            this.totalRows = filteredItems.length;
            this.currentPage = 1;
        },  
    },
}
</script>

