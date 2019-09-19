<template>
    <b-form-group :label="localLabel" v-show="showGroup" :class="{'debug-mode': locationCount < 2 && debugMode}" style="position: relative;" :label-class="required ? 'required' : ''">
        <!-- Debug Notice Allowing Developers to Manually Hide This Form Group To Mimic Single Business Registries -->
        <div class="debug-notice" v-if="debugMode && !hideDebugNotice">
            <span class="hidden-xs-down">This is hidden on production for single business registries.</span><br />
            <div class="debug-links">
                <a href="javascript:void(0)" @click="manualHide=true">Hide Menu</a> | <a href="javascript:void(0)" @click="hideDebugNotice=true">Hide Notice</a>
            </div>
        </div>
        <business-location-select v-model="localValue" @locationCount="setLocationCount" :allow-all="allowAll" :name="name"></business-location-select>
        <input-help :form="form" :field="field" :text="helpText" v-if="form && field"></input-help>
    </b-form-group>
</template>

<script>
    import BusinessLocationSelect from "./BusinessLocationSelect";

    export default {
        name: "BusinessLocationFormGroup",
        components: {BusinessLocationSelect},
        props: ['label', 'name', 'form', 'field', 'value', 'helpText', 'allowAll', 'required'],
        computed: {
            localLabel() {
                return this.label || this.label === null ? this.label : "Office Location";
            },
            localValue: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$emit('input', value);
                }
            },
            showGroup() {
                return !this.manualHide && (this.locationCount > 1 || this.debugMode);
            },
            debugMode() {
                return window.DevelopmentMode;
            }
        },
        data() {
            return {
                locationCount: 0,
                manualHide: false,
                hideDebugNotice: false,
            }
        },
        methods: {
            setLocationCount(count) {
                this.locationCount = count;
            }
        },
    }
</script>

<style scoped>
    .debug-mode {
        background-color: lightyellow;
        position: relative;
    }
    .debug-notice {
        position: absolute;
        z-index: 200;
        top: 0;
        right: 0;
        font-weight: bold;
        font-size: 0.6rem;
    }
    .debug-links {
        float: right;
    }
</style>