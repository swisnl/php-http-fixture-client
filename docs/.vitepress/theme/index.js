import DefaultTheme from 'vitepress/theme';
import UrlHelper from '../components/UrlHelper.vue';

export default {
    ...DefaultTheme,
    enhanceApp({ app }) {
        app.component('UrlHelper', UrlHelper)
    }
}
