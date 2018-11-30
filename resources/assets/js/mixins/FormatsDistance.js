export default {
    data() {
        return {
            conversionRatio: 1609.344,
        };
    },

    methods: {
        convertToMiles(distance, shouldFormat = true) {
            const result = distance / this.conversionRatio;
            return shouldFormat ? Number(result).toFixed(2) : result;
        },

        convertToMeters(distance, shouldFormat = true) {
            const result = distance * this.conversionRatio;
            return shouldFormat ? Number(result).toFixed(2) : result;
        }
    },
}