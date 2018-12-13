<template>
    <div>
        <b-btn @click="show = true" variant="primary">Show or Hide Columns</b-btn>

        <b-modal title="Show or Hide Columns" v-model="show">
            <b-container fluid>
                <b-row>
                    <div class="form-check row">
                        <div class="col-sm-auto" v-for="(field, key) in columns" :key="key">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" @change="onChange(key)" :name="key" :checked="field.shouldShow">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">{{ field.label }}</span>
                            </label>
                        </div>
                    </div>
                </b-row>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="default" @click="show = false">Close</b-btn>
            </div>
        </b-modal>
    </div>
</template>

<script>
import LocalStorage from '../../../mixins/LocalStorage';

export default {
    props: {
        prefix: {
            type: String,
            required: true,
        },
        columns: {
            type: Object,
            required: true,
        },
    },

    mixins: [LocalStorage],

    mounted() {
        const storageFields = this.getLocalStorage('fields');

        if(storageFields) {
            const updated = { ...this.columns };
            Object.keys(updated).forEach(key => {
                if(!storageFields.includes(key)) {
                    updated[key].shouldShow = false;
                }
            });

            this.$emit('update:columns', updated);
        }
    },

    data() {
        return {
            show: false,
            localStoragePrefix: this.prefix,
        };
    },

    methods: {
        onChange(key) {
            const updated = { ...this.columns };
            updated[key].shouldShow = !updated[key].shouldShow;

            this.$emit('update:columns', updated);

            const fields = [];
            Object.keys(this.columns).forEach(key => this.columns[key].shouldShow ? fields.push(key) : null);
            this.setLocalStorage('fields', fields);
        },
    }, 
}
</script>
