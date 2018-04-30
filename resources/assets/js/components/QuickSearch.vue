<template>
    <div class="v-select-container">
        <v-select 
            @input="onSelect"
            label="name"
            :options="options"
            :onSearch="onSearch"
            placeholder="Quick Search"
        >
            <template slot="no-options">
                No Results
            </template>
            <template slot="option" slot-scope="option">
                {{ option.name }}
            </template>
        </v-select>
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
    }),

    methods: {
        onSearch(search, loading) {
            loading(true);
            this.search(search, loading, this);
        },

        search: _.debounce((search, loading, vm) => {
            axios.get('/business/search?q=' + search)
                .then(response => {
                    vm.options = response.data.data;
                    loading(false);
                })
                .catch( e => {
                    vm.options = [];
                    loading(false);
                });
        }, 350),

        onSelect(item) {
            window.location = `/business/${item.role_type}s/${item.id}`;
        },
    }
}

</script>
<style>
/* highlight class is used elsewhere and messes up the selected items styling */
.v-select .highlight { padding: 0px!important; margin: 0px!important; }

.v-select {
    color: #67757c;
    font-size: 1rem;
}
.v-select .dropdown-toggle {
    background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1MTIgNTEyIj48cGF0aCBmaWxsPSIjN2I4MzhiIiBkPSJNNTA1IDQ0Mi43TDQwNS4zIDM0M2MtNC41LTQuNS0xMC42LTctMTctN0gzNzJjMjcuNi0zNS4zIDQ0LTc5LjcgNDQtMTI4QzQxNiA5My4xIDMyMi45IDAgMjA4IDBTMCA5My4xIDAgMjA4czkzLjEgMjA4IDIwOCAyMDhjNDguMyAwIDkyLjctMTYuNCAxMjgtNDR2MTYuM2MwIDYuNCAyLjUgMTIuNSA3IDE3bDk5LjcgOTkuN2M5LjQgOS40IDI0LjYgOS40IDMzLjkgMGwyOC4zLTI4LjNjOS40LTkuNCA5LjQtMjQuNi4xLTM0ek0yMDggMzM2Yy03MC43IDAtMTI4LTU3LjItMTI4LTEyOCAwLTcwLjcgNTcuMi0xMjggMTI4LTEyOCA3MC43IDAgMTI4IDU3LjIgMTI4IDEyOCAwIDcwLjctNTcuMiAxMjgtMTI4IDEyOHoiIGNsYXNzPSIiPjwvcGF0aD48L3N2Zz4=);
    background-position-x: 8px;
    background-position-y: 8px;
    background-size: 20px;
    background-repeat: no-repeat;
    padding-left: 25px;
}
.selected-tag { display: none }
</style>