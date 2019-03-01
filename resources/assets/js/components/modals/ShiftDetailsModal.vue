<template>
    <b-modal id="detailsModal" title="Shift Details" v-model="showModal" size="lg">
        <shift-details v-if="shift.id" :shift="shift"></shift-details>
        <div slot="modal-footer">
            <slot name="buttons" :item="shift">
                <b-btn variant="default" @click="showModal = false">Close</b-btn>
            </slot>
        </div>
    </b-modal>
</template>

<script>
    export default {
        props: {
            value: false,
            shift: {
                type: Object,
                default: () => {},
            },
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
        },
    }
</script>
