<template>
  <div class="risk-assessment-container">
    <div class="risk-header">
      <h2>Evaluación de Riesgo Suicida</h2>
      <div class="status-badge" :class="getStatusClass">
        {{ getStatusText }}
      </div>
    </div>

    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Cargando evaluación de riesgo...</p>
    </div>

    <div v-else-if="error" class="error-container">
      <i class="fas fa-exclamation-circle"></i>
      <p>{{ error }}</p>
      <button @click="loadAssessment" class="retry-btn">
        <i class="fas fa-sync-alt"></i> Reintentar
      </button>
    </div>

    <div v-else-if="assessment" class="assessment-content">
      <div class="risk-summary">
        <div class="risk-level-container">
          <div class="risk-level" :class="getRiskLevelClass">
            <div class="risk-value">{{ (assessment.risk_score * 100).toFixed(0) }}%</div>
            <div class="risk-label">{{ assessment.risk_level }}</div>
          </div>
          <div class="risk-date">
            <div>Fecha de evaluación:</div>
            <div>{{ formatDate(assessment.created_at) }}</div>
          </div>
        </div>

        <div class="patient-summary">
          <h3>Información del Paciente</h3>
          <div class="patient-details" v-if="assessment.patient">
            <div class="detail-row">
              <div class="detail-label">Nombre:</div>
              <div class="detail-value">{{ assessment.patient.nombre_completo }}</div>
            </div>
            <div class="detail-row">
              <div class="detail-label">Documento:</div>
              <div class="detail-value">{{ assessment.patient.documento }}</div>
            </div>
            <div class="detail-row">
              <div class="detail-label">Edad:</div>
              <div class="detail-value">{{ assessment.patient.edad || 'No disponible' }}</div>
            </div>
          </div>
          <div v-else class="no-patient-info">
            Información del paciente no disponible
          </div>
        </div>
      </div>

      <div class="assessment-details">
        <div class="detail-card risk-factors">
          <h3>Factores de Riesgo</h3>
          <ul v-if="assessment.riskFactors && assessment.riskFactors.length > 0">
            <li v-for="(factor, index) in assessment.riskFactors" :key="index">
              {{ factor.description }}
            </li>
          </ul>
          <p v-else class="no-data">No se han identificado factores de riesgo</p>
        </div>

        <div class="detail-card warning-signs">
          <h3>Señales de Alerta</h3>
          <div class="signs-container">
            <div class="critical-signs" v-if="criticalSigns.length > 0">
              <h4>Señales Críticas</h4>
              <ul>
                <li v-for="(sign, index) in criticalSigns" :key="`critical-${index}`" class="critical-sign">
                  <i class="fas fa-exclamation-triangle"></i> {{ sign.description }}
                </li>
              </ul>
            </div>
            <div class="regular-signs" v-if="regularSigns.length > 0">
              <h4>Otras Señales</h4>
              <ul>
                <li v-for="(sign, index) in regularSigns" :key="`regular-${index}`">
                  {{ sign.description }}
                </li>
              </ul>
            </div>
          </div>
          <p v-if="!criticalSigns.length && !regularSigns.length" class="no-data">
            No se han identificado señales de alerta
          </p>
        </div>
      </div>

      <div class="intervention-section" v-if="assessment.interventionGuide">
        <h3>Guía de Intervención</h3>
        <div class="intervention-content">
          <div class="intervention-tabs">
            <div 
              v-for="(tab, index) in interventionTabs" 
              :key="index" 
              @click="activeTab = tab.id"
              class="tab"
              :class="{ active: activeTab === tab.id }">
              <i class="fas" :class="tab.icon"></i> {{ tab.label }}
            </div>
          </div>

          <div class="tab-content">
            <!-- Pasos Inmediatos -->
            <div v-if="activeTab === 'steps'" class="tab-pane">
              <ol class="intervention-list">
                <li v-for="(step, index) in parseJson(assessment.interventionGuide.steps)" :key="index">
                  {{ step }}
                </li>
              </ol>
            </div>

            <!-- Técnicas -->
            <div v-if="activeTab === 'techniques'" class="tab-pane">
              <ul class="intervention-list">
                <li v-for="(technique, index) in parseJson(assessment.interventionGuide.techniques)" :key="index">
                  {{ technique }}
                </li>
              </ul>
            </div>

            <!-- Recursos -->
            <div v-if="activeTab === 'resources'" class="tab-pane">
              <ul class="intervention-list">
                <li v-for="(resource, index) in parseJson(assessment.interventionGuide.resources)" :key="index">
                  {{ resource }}
                </li>
              </ul>
            </div>

            <!-- Plan de Seguimiento -->
            <div v-if="activeTab === 'followup'" class="tab-pane">
              <ul class="intervention-list">
                <li v-for="(item, index) in parseJson(assessment.interventionGuide.follow_up_plan)" :key="index">
                  {{ item }}
                </li>
              </ul>
            </div>

            <!-- Estrategias de Comunicación -->
            <div v-if="activeTab === 'communication'" class="tab-pane">
              <ul class="intervention-list">
                <li v-for="(strategy, index) in parseJson(assessment.interventionGuide.communication_strategies)" :key="index">
                  {{ strategy }}
                </li>
              </ul>
            </div>

            <!-- Plan de Seguridad -->
            <div v-if="activeTab === 'safety'" class="tab-pane">
              <ul class="intervention-list">
                <li v-for="(item, index) in parseJson(assessment.interventionGuide.safety_plan)" :key="index">
                  {{ item }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="action-buttons">
        <button v-if="!assessment.interventionGuide" @click="generateGuide" class="primary-btn" :disabled="generatingGuide">
          <i class="fas fa-first-aid"></i> {{ generatingGuide ? 'Generando...' : 'Generar Guía de Intervención' }}
        </button>
        <button @click="printAssessment" class="secondary-btn">
          <i class="fas fa-print"></i> Imprimir Evaluación
        </button>
        <button @click="updateStatus('reviewed')" class="success-btn" v-if="assessment.status === 'pending'">
          <i class="fas fa-check"></i> Marcar como Revisado
        </button>
        <button @click="updateStatus('archived')" class="warning-btn" v-if="assessment.status !== 'archived'">
          <i class="fas fa-archive"></i> Archivar
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import moment from 'moment';

export default {
  props: {
    assessmentId: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      assessment: null,
      loading: true,
      error: null,
      activeTab: 'steps',
      generatingGuide: false,
      interventionTabs: [
        { id: 'steps', label: 'Pasos Inmediatos', icon: 'fa-list-ol' },
        { id: 'techniques', label: 'Técnicas', icon: 'fa-hands-helping' },
        { id: 'resources', label: 'Recursos', icon: 'fa-medkit' },
        { id: 'followup', label: 'Plan de Seguimiento', icon: 'fa-calendar-check' },
        { id: 'communication', label: 'Estrategias de Comunicación', icon: 'fa-comments' },
        { id: 'safety', label: 'Plan de Seguridad', icon: 'fa-shield-alt' }
      ]
    };
  },
  computed: {
    getStatusClass() {
      if (!this.assessment) return '';
      
      switch (this.assessment.status) {
        case 'pending': return 'status-pending';
        case 'reviewed': return 'status-reviewed';
        case 'archived': return 'status-archived';
        default: return '';
      }
    },
    getStatusText() {
      if (!this.assessment) return '';
      
      switch (this.assessment.status) {
        case 'pending': return 'Pendiente de Revisión';
        case 'reviewed': return 'Revisado';
        case 'archived': return 'Archivado';
        default: return 'Estado Desconocido';
      }
    },
    getRiskLevelClass() {
      if (!this.assessment) return '';
      
      const level = this.assessment.risk_level.toLowerCase();
      if (level.includes('bajo')) return 'risk-low';
      if (level.includes('moderado')) return 'risk-medium';
      if (level.includes('alto')) return 'risk-high';
      if (level.includes('crítico') || level.includes('critico')) return 'risk-critical';
      return 'risk-unknown';
    },
    criticalSigns() {
      if (!this.assessment || !this.assessment.warningSigns) return [];
      return this.assessment.warningSigns.filter(sign => sign.is_critical);
    },
    regularSigns() {
      if (!this.assessment || !this.assessment.warningSigns) return [];
      return this.assessment.warningSigns.filter(sign => !sign.is_critical);
    }
  },
  mounted() {
    this.loadAssessment();
  },
  methods: {
    async loadAssessment() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.get(`/api/risk/assessment/${this.assessmentId}`);
        if (response.data.success) {
          this.assessment = response.data.assessment;
        } else {
          this.error = 'No se pudo cargar la evaluación de riesgo';
        }
      } catch (error) {
        console.error('Error loading risk assessment:', error);
        this.error = 'Error al cargar la evaluación: ' + (error.response?.data?.message || error.message);
      } finally {
        this.loading = false;
      }
    },
    
    async generateGuide() {
      this.generatingGuide = true;
      
      try {
        const response = await axios.post('/api/risk/assessment/intervention-guide', {
          assessment_id: this.assessmentId
        });
        
        if (response.data.success) {
          // Actualizar la evaluación con la nueva guía
          this.assessment.interventionGuide = response.data.guide;
          this.$toasted.success('Guía de intervención generada correctamente');
        } else {
          this.$toasted.error('Error al generar la guía de intervención');
        }
      } catch (error) {
        console.error('Error generating intervention guide:', error);
        this.$toasted.error('Error al generar la guía: ' + (error.response?.data?.message || error.message));
      } finally {
        this.generatingGuide = false;
      }
    },
    
    async updateStatus(status) {
      try {
        const response = await axios.put(`/api/risk/assessment/${this.assessmentId}/status`, { status });
        
        if (response.data.success) {
          this.assessment.status = status;
          this.$toasted.success(`Estado actualizado a "${this.getStatusText}"`);
        } else {
          this.$toasted.error('Error al actualizar el estado');
        }
      } catch (error) {
        console.error('Error updating status:', error);
        this.$toasted.error('Error al actualizar el estado: ' + (error.response?.data?.message || error.message));
      }
    },
    
    printAssessment() {
      window.print();
    },
    
    parseJson(jsonString) {
      if (!jsonString) return [];
      
      try {
        return JSON.parse(jsonString);
      } catch (error) {
        console.error('Error parsing JSON:', error);
        return [];
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      return moment(dateString).format('DD/MM/YYYY HH:mm');
    }
  }
};
</script>

<style scoped>
.risk-assessment-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  background-color: #f8f9fa;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.risk-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #e1e5e9;
}

.risk-header h2 {
  margin: 0;
  color: #2c3e50;
  font-size: 1.8rem;
}

.status-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 500;
}

.status-pending {
  background-color: #f39c12;
  color: white;
}

.status-reviewed {
  background-color: #2ecc71;
  color: white;
}

.status-archived {
  background-color: #95a5a6;
  color: white;
}

.loading-container, .error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  text-align: center;
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 5px solid #f3f3f3;
  border-top: 5px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 15px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-container {
  color: #e74c3c;
}

.error-container i {
  font-size: 3rem;
  margin-bottom: 15px;
}

.retry-btn {
  margin-top: 15px;
  padding: 8px 16px;
  background-color: #3498db;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
}

.risk-summary {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin-bottom: 30px;
}

.risk-level-container {
  flex: 1;
  min-width: 300px;
  display: flex;
  align-items: center;
  gap: 25px;
}

.risk-level {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  color: white;
  text-align: center;
}

.risk-low {
  background-color: #2ecc71;
}

.risk-medium {
  background-color: #f39c12;
}

.risk-high {
  background-color: #e74c3c;
}

.risk-critical {
  background-color: #c0392b;
}

.risk-unknown {
  background-color: #95a5a6;
}

.risk-value {
  font-size: 2rem;
  font-weight: bold;
  line-height: 1;
}

.risk-label {
  font-size: 0.9rem;
  text-transform: uppercase;
  margin-top: 5px;
}

.risk-date {
  font-size: 0.9rem;
  color: #7f8c8d;
}

.patient-summary {
  flex: 2;
  min-width: 300px;
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.patient-summary h3 {
  margin-top: 0;
  margin-bottom: 15px;
  color: #2c3e50;
  font-size: 1.2rem;
  border-bottom: 1px solid #ecf0f1;
  padding-bottom: 10px;
}

.detail-row {
  display: flex;
  margin-bottom: 10px;
}

.detail-label {
  min-width: 100px;
  font-weight: 500;
  color: #7f8c8d;
}

.detail-value {
  flex: 1;
}

.assessment-details {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin-bottom: 30px;
}

.detail-card {
  flex: 1;
  min-width: 300px;
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.detail-card h3 {
  margin-top: 0;
  margin-bottom: 15px;
  color: #2c3e50;
  font-size: 1.2rem;
  border-bottom: 1px solid #ecf0f1;
  padding-bottom: 10px;
}

.detail-card h4 {
  margin: 15px 0 10px;
  color: #34495e;
  font-size: 1rem;
}

.detail-card ul {
  padding-left: 20px;
  margin: 0;
}

.detail-card li {
  margin-bottom: 8px;
  line-height: 1.5;
}

.critical-sign {
  color: #e74c3c;
}

.critical-sign i {
  margin-right: 5px;
}

.no-data {
  color: #95a5a6;
  font-style: italic;
}

.intervention-section {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 20px;
  margin-bottom: 30px;
}

.intervention-section h3 {
  margin-top: 0;
  margin-bottom: 15px;
  color: #2c3e50;
  font-size: 1.2rem;
  border-bottom: 1px solid #ecf0f1;
  padding-bottom: 10px;
}

.intervention-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  margin-bottom: 15px;
}

.tab {
  padding: 8px 15px;
  background-color: #ecf0f1;
  border-radius: 20px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.2s;
}

.tab i {
  margin-right: 5px;
}

.tab:hover {
  background-color: #d6dbdf;
}

.tab.active {
  background-color: #3498db;
  color: white;
}

.tab-content {
  margin-top: 20px;
}

.tab-pane {
  padding: 15px;
  background-color: #f8f9fa;
  border-radius: 8px;
}

.intervention-list {
  margin: 0;
  padding-left: 25px;
}

.intervention-list li {
  margin-bottom: 10px;
  line-height: 1.6;
}

.action-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: flex-end;
}

.action-buttons button {
  padding: 10px 15px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background-color 0.2s;
}

.primary-btn {
  background-color: #3498db;
  color: white;
}

.primary-btn:hover {
  background-color: #2980b9;
}

.secondary-btn {
  background-color: #95a5a6;
  color: white;
}

.secondary-btn:hover {
  background-color: #7f8c8d;
}

.success-btn {
  background-color: #2ecc71;
  color: white;
}

.success-btn:hover {
  background-color: #27ae60;
}

.warning-btn {
  background-color: #f39c12;
  color: white;
}

.warning-btn:hover {
  background-color: #e67e22;
}

button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .risk-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  
  .risk-level-container {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .tab {
    font-size: 0.8rem;
    padding: 6px 10px;
  }
  
  .action-buttons {
    justify-content: center;
  }
}

@media print {
  .action-buttons, .tab {
    display: none;
  }
  
  .tab-pane {
    display: block !important;
    page-break-inside: avoid;
  }
  
  .intervention-section h3 {
    page-break-before: always;
  }
}
</style>
