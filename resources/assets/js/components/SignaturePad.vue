<template>
    <div>
        <button @click="draw()" type="button" 
                class="btn" :class="signature.length ? 'btn-info' : 'btn-outline-info'">
            <span v-if="signature.length">
                <i class="fa fa-check"></i>&nbsp;&nbsp;&nbsp;Signed
            </span>
            <span v-else>Add a Signature</span>
        </button>
        
        <input type="hidden" name="signature" v-model="signature" />
        <img :src="preview" class="img-responsive" v-if="preview" />
        
        <div class="signature-pad" v-show="signing">
            <canvas :id="uid" class="canvas" :data-uid="uid"></canvas>
            <div class="dotted-line"></div>
            <div class="actions">
                <button @click="clear()" type="button" class="btn btn-secondary">
                    Clear Signature
                </button>
                <button @click="save()" type="button" class="btn btn-success">
                    Done Signing
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    import SignaturePad from 'signature_pad'
    export default {
        name:"signaturePad",
        props: {
            sigOption: {
                type:Object,
                default:()=>{penColor : 'rgb(0, 0, 0)'},
            },
            w:{
                type:String,
                default:"100%"
            },
            h:{
                type:String,
                default:"100%"
            }
        },
        data() {
            return {
                sig: ()=>{},
                uid: "",
                signing: false,
                canvas: null,
                preview: "",
                signature: "",
            }
        },
        created() {
            this.uid = "canvas" + this._uid
        },
        methods: {
            checkOrientation() {
                if (window.innerHeight > window.innerWidth) {
                    alert("You are now in portrait");
                } else {
                    alert("You are now in landscape");
                }
            },
            draw() {
                this.signing = true;
                this.$nextTick(() => {
                    this.canvas = document.getElementById(this.uid);
                    this.sig = new SignaturePad(this.canvas, this.sigOption);                
                    window.addEventListener("resize", this.resizeCanvas);
                    this.resizeCanvas();
                });
            },
            resizeCanvas() {
                var ratio =  Math.max(window.devicePixelRatio || 1, 1);
                this.canvas.width = this.canvas.offsetWidth * ratio;
                this.canvas.height = this.canvas.offsetHeight * ratio;
                this.canvas.getContext("2d").scale(ratio, ratio);
                this.clear();
            },
            clear() {                
                this.sig.clear();
            },
            save(format = "image/svg+xml") {
                var dataUrl = format ? this.sig.toDataURL(format) :  this.sig.toDataURL()                
                var blob = atob(dataUrl.split(',')[1])
                //console.log(blob);
                this.signature = blob;
                this.signing = false;                                               
                this.preview = this.sig.toDataURL()
                this.$emit('input', this.signature)
                /*
                    signaturePad.toDataURL(); // save image as PNG
                    signaturePad.toDataURL("image/jpeg"); // save image as JPEG
                    signaturePad.toDataURL("image/svg+xml"); // save image as SVG
                */
            }
        }
    }
</script>

<style>
    .signature-pad {        
        position: fixed;
        height: 100%;
        width: 100%;
        top: 0;
        left: 0;
        background: #eeeeee;
        z-index: 999;
        text-align: center;
    }
    .dotted-line {
        border-bottom: 2px dashed #dddddd;
        max-width: 650px;
        position: relative;
        margin: auto;
        margin-top: -80px;
        margin-bottom: 80px;
    }
    canvas {
        width: 100%;
        height: 80%;
        max-width: 800px;
        max-height: 300px;
        background: white;
        margin: auto;
        z-index: 9999;
    }
    .actions {
        position: relative;
        bottom: 0px;
    }
</style>