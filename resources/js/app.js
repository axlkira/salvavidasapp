require('./bootstrap');

import { createApp } from 'vue';
import axios from 'axios';
import Toasted from 'vue-toasted';
import moment from 'moment';
import 'moment/locale/es';

// Configurar moment en español
moment.locale('es');

// Configurar axios para incluir el token CSRF en todas las solicitudes
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Importar componentes
import Chat from './components/Chat.vue';
import PatientInfo from './components/PatientInfo.vue';
import RiskAssessment from './components/RiskAssessment.vue';
import RiskAssessmentList from './components/RiskAssessmentList.vue';

// Inicializar la aplicación Vue
const app = createApp({});

// Registrar componentes globalmente
app.component('chat', Chat);
app.component('patient-info', PatientInfo);
app.component('risk-assessment', RiskAssessment);
app.component('risk-assessment-list', RiskAssessmentList);

// Usar plugins
app.use(Toasted, {
    duration: 3000,
    position: 'top-right',
    theme: 'toasted-primary',
    iconPack: 'fontawesome'
});

// Montar la aplicación
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('app')) {
        app.mount('#app');
    }
});
