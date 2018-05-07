<template>
    <div class="quick-search">
        <div class="form-control icon-control">
            <i v-if="loading" class="fa fa-spinner fa-spin"></i>
            <i v-else class="fa fa-search"></i>
            <input type="text" 
                placeholder="Quick Search" 
                v-model="filter" 
                @input="onSearch" 
                @focus="showResults()" 
                @blur="hideResults()" 
            />
        </div>
        <div v-show="isSearching" class="search-results">
            <b-dropdown-item v-if="options.length == 0">No Results</b-dropdown-item>
            <b-dropdown-item v-else v-for="item in options" :key="item.id" :href="`/business/${item.role_type}s/${item.id}`">
                {{ item.name }}
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
.quick-search {
    width: 300px;
    position: relative;
    float: right;
}

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

.search-results {
    width: 300px;
    position: absolute;
    max-height: 300px; 
    display: block;
    top: 100%;
    left: 0;
    z-index: 1000;
    min-width: 160px;
    padding: 5px 0;
    margin: 0;
    width: 100%;
    overflow-y: scroll;
    border: 1px solid rgba(0,0,0,.26);
    box-shadow: 0 3px 6px 0 rgba(0,0,0,.15);
    border-top: none;
    border-radius: 0 0 4px 4px;
    text-align: left;
    list-style: none;
    background: #fff;
}
</style>