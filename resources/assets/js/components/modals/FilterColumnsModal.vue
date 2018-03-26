<template>
    <b-modal id="filterColumnsModal" title="Show or Hide Columns" v-model="showModal">
        <b-container fluid>
            <b-row>
                <div class="form-check row">
                    <div class="col-sm-auto" v-for="field in availableFields">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" v-model="localFields" :value="field">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description">{{ field }}</span>
                        </label>
                    </div>
                </div>
            </b-row>
        </b-container>
        <div slot="modal-footer">
            <b-btn variant="default" @click="showModal=false">Close</b-btn>
        </div>
    </b-modal>
</template>

<script>
    export default {
        props: {
            value: {},
            availableFields: Array,
            fields: Array,
        },

        computed: {
            showModal: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$emit('input', value);
                }
            },
            localFields: {
                get() {
                    return this.fields;
                },
                set(value) {
                    this.$emit('update:fields', value);
                }
            }
        },
    }
</script>
