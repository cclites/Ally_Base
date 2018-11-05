<template>
    <div>
        <portal to="page-title-right">
            <div class="quick-search">
                <div class="form-control icon-control">
                    <i v-if="searching" class="fa fa-spinner fa-spin"></i>
                    <i v-else class="fa fa-search"></i>
                    <input type="text"
                           placeholder="Search Knowledge Base"
                           v-model="filter"
                           @input="onSearch"
                           @focus="showResults()"
                           @blur="hideResults()"
                    />
                </div>
                <div v-show="openSearch" class="search-results">
                    <b-dropdown-item v-if="searchResults.length == 0">No Results</b-dropdown-item>
                    <b-dropdown-item v-else
                                     v-for="item in searchResults"
                                     :key="item.id"
                                     :href="`#${item.slug}`"
                                     class="search-result"
                    >
                        {{ item.title }}
                    </b-dropdown-item>
                </div>
            </div>
        </portal>
        <b-row class="mb-4">
            <b-col lg="4" md="12" class="mb-4">
                <b-list-group>
                    <b-list-group-item><h3>FAQ</h3></b-list-group-item>

                    <b-list-group-item v-for="item in faq" :key="item.id">
                        <div class="d-none d-sm-none d-md-none d-lg-block d-xl-block">
                            <span v-if="item.slug == hash">{{ item.title }}</span>
                            <a v-else :href="`#${item.slug}`" role="tab" >{{ item.title }}</a>
                        </div>
                        <div class="d-sm-block d-md-block d-lg-none d-xl-none">
                            <a v-b-toggle="`${item.slug}`" href="" @click="e => e.preventDefault()">{{ item.title }}</a>
                            <b-collapse :id="item.slug" class="mt-3">
                                <!-- <b-card> -->
                                <knowledge-item :item="item" :hide-title="true" />
                                <!-- </b-card> -->
                            </b-collapse>
                        </div>
                    </b-list-group-item>

                    <b-list-group-item><h3>Tutorials</h3></b-list-group-item>

                    <b-list-group-item v-for="item in tutorials" :key="item.id">
                        <div class="d-none d-sm-none d-md-none d-lg-block d-xl-block">
                            <span v-if="item.slug == hash">{{ item.title }}</span>
                            <a v-else :href="`#${item.slug}`" role="tab" >{{ item.title }}</a>
                        </div>
                        <div class="d-sm-block d-md-block d-lg-none d-xl-none">
                            <a v-b-toggle="`${item.slug}`" href="" @click="e => e.preventDefault()">{{ item.title }}</a>
                            <b-collapse :id="item.slug" class="mt-3">
                                <!-- <b-card> -->
                                <knowledge-item :item="item" :hide-title="true" />
                                <!-- </b-card> -->
                            </b-collapse>
                        </div>
                    </b-list-group-item>

                    <b-list-group-item><h3>Resources</h3></b-list-group-item>

                    <b-list-group-item v-for="item in resources" :key="item.id">
                        <div class="d-none d-sm-none d-md-none d-lg-block d-xl-block">
                            <span v-if="item.slug == hash">{{ item.title }}</span>
                            <a v-else :href="`#${item.slug}`" role="tab" >{{ item.title }}</a>
                        </div>
                        <div class="d-sm-block d-md-block d-lg-none d-xl-none">
                            <a v-b-toggle="`${item.slug}`" href="" @click="e => e.preventDefault()">{{ item.title }}</a>
                            <b-collapse :id="item.slug" class="mt-3">
                                <!-- <b-card> -->
                                <knowledge-item :item="item" :hide-title="true" />
                                <!-- </b-card> -->
                            </b-collapse>
                        </div>
                    </b-list-group-item>
                </b-list-group>
            </b-col>
            <b-col lg="8" class="d-none d-sm-none d-md-none d-lg-block d-xl-block">
                <b-card>
                    <div class="tab-content" v-if="hash">
                        <div v-for="item in knowledgeBase" :key="item.id" class="tab-pane" :class="{'active': hash == item.slug}" :id="item.slug" role="tabpanel">
                            <knowledge-item :item="item" />
                        </div>
                    </div>
                    <div v-else class="text-center">
                        <h3>Browse for help</h3>
                    </div>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    export default {
        props: ['knowledgeBase'],

        data() {
            return {
                searchResults: [],
                filter: '',
                openSearch: false,
                searching: false,
                hash: '',
            };
        },

        computed: {
            faq() {
                return this.knowledgeBase.filter(item => item.type == 'faq');
            },

            tutorials() {
                return this.knowledgeBase.filter(item => item.type == 'tutorial');
            },

            resources() {
                return this.knowledgeBase.filter(item => item.type == 'resource');
            },
        },

        methods: {
            onSearch() {
                this.showResults()
                if (this.filter == '') {
                    this.searchResults = [];
                    return;
                }
                this.searching = true;
                this.search(this.filter, this);
            },

            search: _.debounce((search, vm) => {
                axios.get('/knowledge-base?q=' + search)
                    .then(response => {
                        vm.showResults();
                        vm.searchResults = response.data || [];
                        vm.searching = false;
                    })
                    .catch( e => {
                        vm.showResults(false);
                        vm.searchResults = [];
                        vm.searching = false;
                    });
            }, 350),

            showResults(show = true) {
                if (this.filter == '') {
                    show = false;
                }
                this.openSearch = show;
            },

            hideResults() {
                setTimeout(function() {
                    this.openSearch = false;
                }.bind(this), 200);
            },

            changeUrl(e) {
                this.hash = window.location.hash ? window.location.hash.substr(1) : '';
                window.scroll(0, 0);
            },
        },

        mounted() {
            this.hash = window.location.hash ? window.location.hash.substr(1) : '';
            window.addEventListener("hashchange", this.changeUrl, false);

            // Hide the other quick search on the page, if it exists
            $('.quick-search').not('.vue-portal-target .quick-search').hide();
        },
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
