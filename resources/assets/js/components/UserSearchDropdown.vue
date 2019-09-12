<template>
    <div style="position: relative">
        <div class="form-control icon-control">
            <i v-if="loading" class="fa fa-spinner fa-spin"></i>
            <i v-else :class="`fa ${icon}`"></i>
            <input type="text" autocomplete="off" readonly onfocus="javascript: this.removeAttribute('readonly')" onblur="javascript: this.setAttribute('readonly', true )"
                name="search-term"
                id="search-term"
                :placeholder="placeholder"
                v-model="filter"
                @input="onSearch" 
                @focus="showResults()" 
                @blur="hideResults()" 
            />
        </div>

        <div v-show="isSearching" class="search-results">
            <b-dropdown-item v-if="options.length == 0">{{ noResultsText }}</b-dropdown-item>
            <b-dropdown-item v-else v-for="item in options" :key="item.id" @click.prevent="onClick(item)" @focus.prevent="onClick(item)">
                {{ formatter(item) }}
            </b-dropdown-item>
        </div>
    </div>
</template>

<script>
export default {
    name: 'UserSearchDropdown',

    props: {
        role: {
            type: String,
            default: '',
        },
        type: {
            type: String,
            default: 'role'
        },
        placeholder: {
            type: String,
            default: 'Search Clients and Caregivers',
        },
        icon: {
            type: String,
            default: 'fa-search',
        },
        noResultsText: {
            type: String,
            default: 'No Results',
        },
        clearOnSubmit: {
            type: Boolean,
            default: false,
        },
        formatter: {
            type: Function,
            default: (item) => {
                return `${item.name} - ${_.capitalize(item.role_type)}`
            }
        }
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
            axios.get('/business/search?type=' + vm.type + '&q=' + search + '&role=' + vm.role)
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
        },

        onClick(user) {
            if (this.clearOnSubmit) {
                this.filter = '';
                this.options = [];
            }

            this.$emit('selectUser', user);
        },
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
