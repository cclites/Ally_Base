<template>

    <b-row>

        <b-col sm="6">

            <b-form-select :disabled=" false ">
                <option value="">--None--</option>
                <option v-for=" ( item, index ) in [] " :value=" item " :key=" index ">{{ item }}</option>
            </b-form-select>
        </b-col>
        <b-col sm="6">

            <b-form-select :disabled=" false ">
                <option value="">--None--</option>
                <option v-for=" ( item, index ) in [] " :value=" item " :key=" index ">{{ item }}</option>
            </b-form-select>
        </b-col>
    </b-row>
</template>

<script>

    export default {

    }
</script>

<style>

</style>