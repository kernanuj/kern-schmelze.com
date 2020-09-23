import tsImageDe from './ts-image_de.png';
import tsImageEn from './ts-image_en.png';

Shopware.Component.register('ts-image', {
    template: '<img :src="imgUrl" style="width: 100%; max-width: 800px; margin: 0px auto 40px auto; display: block;" />',

    computed: {
        imgUrl() {
            if( this.$store.state.adminLocale.currentLocale === 'de-DE' ) {
                return tsImageDe;
            }
            return tsImageEn;
        }
    }
});
