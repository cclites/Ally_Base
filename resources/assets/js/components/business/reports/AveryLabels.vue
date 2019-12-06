<template>
    <b-card title="Avery Label Report">
        <loading-card v-show=" loading " />
        <div v-show=" !loading" class="table-responsive">

            hey lmao
            <!-- <ally-table id="avery-labels" :columns=" fields " :items="items" sort-by="nameLastFirst">

                <template slot="name" scope="data">
                    <a :href="`/business/${type}s/${data.item.id}`">{{ data.item.name }}</a>
                </template>
            </ally-table> -->
        </div>
    </b-card>
</template>

<script>


    export default {

        mixins: [],

        props: {

        },

        data() {

            return {

                loading: false
            }
        },
    }
</script>

<style scoped>
</style>

