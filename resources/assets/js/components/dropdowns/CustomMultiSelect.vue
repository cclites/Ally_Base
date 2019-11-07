<template>
    <b-col class="outer">
        <div class="inner">
            <b-form-select v-model="selected" :options="options" multiple :select-size="selectSize">
                <template slot="first">
                    <option value="">{{ emptyText }}</option>
                </template>
            </b-form-select>
        </div>
    </b-col>
</template>

<script>
    export default {
        name: "CustomMultiSelect",
        props: {
            options: {
                type: Array,
                default: [],
            },
            emptyText: ''
        },
        data(){
            return {
                selected: [],
                selectSize: 2
            }
        }
    }
</script>

<style scoped>

    .outer{
        margin-left: -12px;
    }

</style>