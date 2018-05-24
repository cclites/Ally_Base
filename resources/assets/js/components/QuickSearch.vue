<template>
    <div class="quick-search">
        <div class="form-control icon-control">
            <i v-if="loading" class="fa fa-spinner fa-spin"></i>
            <i v-else class="fa fa-search"></i>
            <input type="text" 
                placeholder="Quick Search Clients and Caregivers"
                v-model="filter" 
                @input="onSearch" 
                @focus="showResults()" 
                @blur="hideResults()" 
            />
        </div>
        <div v-show="isSearching" class="search-results">
            <b-dropdown-item v-if="options.length == 0">No Results</b-dropdown-item>
            <b-dropdown-item v-else v-for="item in options" :key="item.id" :href="`/business/${item.role_type}s/${item.id}`">
                {{ item.name }} - {{ item.role_type | capitalize }}
            </b-dropdown-item>
        </div>
    </div>
</template>

<script>
import vSelect from 'vue-select';

export default {
    name: 'QuickSearch',

    components: {
        'v-select': vSelect
    },

    data: () => ({
        options: [],
        filter: '',
        isSearching: false,
        loading: false,
    }),

    methods: {
        onSearch() {
            this.showResults()
            if (this.filter == '') {
                this.options = [];
                return;
            }
            this.loading = true;
            this.search(this.filter, this);
        },

        search: _.debounce((search, vm) => {
            axios.get('/business/search?q=' + search)
                .then(response => {
                    vm.showResults();
                    vm.options = response.data.data || [];
                    vm.loading = false;
                })
                .catch( e => {
                    vm.showResults(false);
                    vm.options = [];
                    vm.loading = false;
                });
        }, 350),

        showResults(show = true) {
            if (this.filter == '') {
                show = false;
            }
            this.isSearching = show;
        },

        hideResults() {
            setTimeout(function() {
                this.isSearching = false;
            }.bind(this), 200);
        }
    }
}

</script>
<style>
.icon-control {
    position: relative;
    width:100%;
    display: block;
    padding-top: 0px;
    padding-bottom: 0px;
}

.icon-control input {
    border: 0;
    color: #67757c;
    min-height: 38px;
    width: 90%;
}

.icon-control input::placeholder {
    color: #88979e;
}
</style>