<template>
  <div class="patient-info-container">
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Cargando información del paciente...</p>
    </div>
    
    <div v-else-if="error" class="error-container">
      <i class="fas fa-exclamation-circle"></i>
      <p>{{ error }}</p>
    </div>
    
    <div v-else-if="patient" class="patient-data">
      <div class="patient-header">
        <h3>Información del Paciente</h3>
        <div class="risk-badge" v-if="latestAssessment" :class="getRiskLevelClass">
          {{ getRiskLevelText }}
        </div>
      </div>
      
      <div class="patient-details">
        <div class="detail-group">
          <div class="detail-label">Nombre:</div>
          <div class="detail-value">{{ patient.nombre_completo }}</div>
        </div>
        
        <div class="detail-group">
          <div class="detail-label">Documento:</div>
          <div class="detail-value">{{ patient.documento }}</div>
        </div>
        
        <!-- Si hay historia clínica -->
        <div v-if="latestHistory" class="clinical-info">
          <h4>Última Historia Clínica</h4>
          
          <div class="detail-group">
            <div class="detail-label">Fecha:</div>
            <div class="detail-value">{{ formatDate(latestHistory.FechaInicio) }}</div>
          </div>
          
          <div class="detail-group">
            <div class="detail-label">Diagnóstico:</div>
            <div class="detail-value">{{ truncateText(latestHistory.ImprecionDiagnostica) }}</div>
          </div>
          
          <div class="expandable-section">
            <div class="section-header" @click="toggleProblematics">
              <i class="fas" :class="showProblematics ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
              <span>Problemática Actual</span>
            </div>
            <div class="section-content" v-show="showProblematics">
              <p>{{ latestHistory.ProblematicaActual || 'No disponible' }}</p>
            </div>
          </div>
          
          <div class="expandable-section">
            <div class="section-header" @click="toggleObservations">
              <i class="fas" :class="showObservations ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
              <span>Observaciones</span>
            </div>
            <div class="section-content" v-show="showObservations">
              <p>{{ latestHistory.Observaciones || 'No disponible' }}</p>
            </div>
          </div>
        </div>
        
        <!-- Evaluación de riesgo -->
        <div v-if="latestAssessment" class="risk-assessment">
          <h4>Evaluación de Riesgo</h4>
          <div class="risk-meter">
            <div class="risk-bar">
              <div class="risk-fill" :style="{ width: `${latestAssessment.risk_score * 100}%`, backgroundColor: getRiskColor }"></div>
            </div>
            <div class="risk-value">{{ (latestAssessment.risk_score * 100).toFixed(0) }}%</div>
          </div>
          
          <div class="expandable-section">
            <div class="section-header" @click="toggleRiskFactors">
              <i class="fas" :class="showRiskFactors ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
              <span>Factores de Riesgo</span>
            </div>
            <div class="section-content" v-show="showRiskFactors">
              <ul v-if="latestAssessment.risk_factors && latestAssessment.risk_factors.length > 0">
                <li v-for="(factor, index) in latestAssessment.risk_factors" :key="index">
                  {{ factor.description }}
                </li>
              </ul>
              <p v-else>No se han identificado factores de riesgo.</p>
            </div>
          </div>
          
          <div class="expandable-section">
            <div class="section-header" @click="toggleWarnings">
              <i class="fas" :class="showWarnings ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
              <span>Señales de Alerta</span>
            </div>
            <div class="section-content" v-show="showWarnings">
              <ul v-if="criticalSigns && criticalSigns.length > 0">
                <li v-for="(sign, index) in criticalSigns" :key="`critical-${index}`" class="critical-sign">
                  <i class="fas fa-exclamation-triangle"></i> {{ sign.description }}
                </li>
              </ul>
              <ul v-if="normalSigns && normalSigns.length > 0">
                <li v-for="(sign, index) in normalSigns" :key="`normal-${index}`">
                  {{ sign.description }}
                </li>
              </ul>
              <p v-if="(!criticalSigns || criticalSigns.length === 0) && (!normalSigns || normalSigns.length === 0)">
                No se han identificado señales de alerta.
              </p>
            </div>
          </div>
          
          <div class="actions">
            <button @click="viewFullAssessment" class="action-btn">
              <i class="fas fa-file-medical"></i> Ver Evaluación Completa
            </button>
            <button @click="generateIntervention" class="action-btn" :disabled="isGeneratingGuide">
              <i class="fas fa-first-aid"></i> {{ latestAssessment.interventionGuide ? 'Ver Guía' : 'Generar Guía' }}
            </button>
          </div>
        </div>
        
        <!-- Botón para analizar riesgo si no hay evaluación -->
        <div v-else class="risk-actions">
          <button @click="analyzeRisk" class="analyze-btn" :disabled="isAnalyzing">
            <i class="fas fa-chart-bar"></i> Analizar Riesgo
          </button>
        </div>
      </div>
    </div>
    
    <!-- Modal para intervención -->
    <div v-if="showInterventionModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Guía de Intervención</h3>
          <button @click="closeInterventionModal" class="close-btn">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <div v-if="loadingGuide" class="loading-container">
            <div class="loading-spinner"></div>
            <p>Generando guía de intervención...</p>
          </div>
          <div v-else-if="interventionGuide">
            <div class="guide-section">
              <h4>Pasos Inmediatos</h4>
              <ol>
                <li v-for="(step, index) in parseJsonArray(interventionGuide.steps)" :key="`step-${index}`">
                  {{ step }}
                </li>
              </ol>
            </div>
            
            <div class="guide-section">
              <h4>Técnicas Recomendadas</h4>
              <ul>
                <li v-for="(technique, index) in parseJsonArray(interventionGuide.techniques)" :key="`tech-${index}`">
                  {{ technique }}
                </li>
              </ul>
            </div>
            
            <div class="guide-section">
              <h4>Recursos a Considerar</h4>
              <ul>
                <li v-for="(resource, index) in parseJsonArray(interventionGuide.resources)" :key="`res-${index}`">
                  {{ resource }}
                </li>
              </ul>
            </div>
            
            <div class="guide-section">
              <h4>Plan de Seguimiento</h4>
              <ul>
                <li v-for="(plan, index) in parseJsonArray(interventionGuide.follow_up_plan)" :key="`plan-${index}`">
                  {{ plan }}
                </li>
              </ul>
            </div>
            
            <div class="guide-section">
              <h4>Estrategias de Comunicación</h4>
              <ul>
                <li v-for="(strategy, index) in parseJsonArray(interventionGuide.communication_strategies)" :key="`com-${index}`">
                  {{ strategy }}
                </li>
              </ul>
            </div>
            
            <div class="guide-section">
              <h4>Plan de Seguridad</h4>
              <ul>
                <li v-for="(safety, index) in parseJsonArray(interventionGuide.safety_plan)" :key="`safety-${index}`">
                  {{ safety }}
                </li>
              </ul>
            </div>
          </div>
          <div v-else class="error-container">
            <i class="fas fa-exclamation-circle"></i>
            <p>No se pudo generar la guía de intervención. Por favor, intente nuevamente.</p>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="printIntervention" class="action-btn">
            <i class="fas fa-print"></i> Imprimir
          </button>
          <button @click="closeInterventionModal" class="action-btn secondary">
            <i class="fas fa-times"></i> Cerrar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import moment from 'moment';

export default {
  props: {
    patientDocument: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      patient: null,
      latestHistory: null,
      latestAssessment: null,
      loading: true,
      error: null,
      isAnalyzing: false,
      isGeneratingGuide: false,
      showProblematics: false,
      showObservations: false,
      showRiskFactors: false,
      showWarnings: false,
      showInterventionModal: false,
      interventionGuide: null,
      loadingGuide: false
    };
  },
  computed: {
    getRiskLevelClass() {
      if (!this.latestAssessment) return '';
      
      const level = this.latestAssessment.risk_level.toLowerCase();
      if (level.includes('alto') || level.includes('crítico') || level.includes('critico')) {
        return 'risk-high';
      } else if (level.includes('moderado') || level.includes('medio')) {
        return 'risk-medium';
      } else if (level.includes('bajo')) {
        return 'risk-low';
      }
      return 'risk-unknown';
    },
    getRiskLevelText() {
      if (!this.latestAssessment) return '';
      
      const level = this.latestAssessment.risk_level.toLowerCase();
      if (level.includes('alto')) {
        return 'Riesgo Alto';
      } else if (level.includes('crítico') || level.includes('critico')) {
        return 'Riesgo Crítico';
      } else if (level.includes('moderado') || level.includes('medio')) {
        return 'Riesgo Moderado';
      } else if (level.includes('bajo')) {
        return 'Riesgo Bajo';
      }
      return 'Riesgo Indeterminado';
    },
    getRiskColor() {
      if (!this.latestAssessment) return '#ccc';
      
      const level = this.latestAssessment.risk_level.toLowerCase();
      if (level.includes('alto') || level.includes('crítico') || level.includes('critico')) {
        return '#e74c3c';
      } else if (level.includes('moderado') || level.includes('medio')) {
        return '#f39c12';
      } else if (level.includes('bajo')) {
        return '#2ecc71';
      }
      return '#95a5a6';
    },
    criticalSigns() {
      if (!this.latestAssessment || !this.latestAssessment.warning_signs) return [];
      return this.latestAssessment.warning_signs.filter(sign => sign.is_critical);
    },
    normalSigns() {
      if (!this.latestAssessment || !this.latestAssessment.warning_signs) return [];
      return this.latestAssessment.warning_signs.filter(sign => !sign.is_critical);
    }
  },
  mounted() {
    this.loadPatientData();
  },
  methods: {
    async loadPatientData() {
      this.loading = true;
      this.error = null;
      
      try {
        // Aquí harías la llamada a tu API para obtener datos del paciente
        // Por ahora simularemos una respuesta
        const patientResponse = await axios.get(`/api/patients/${this.patientDocument}`);
        if (patientResponse.data.success) {
          this.patient = patientResponse.data.patient;
          
          // Cargar la historia clínica más reciente
          const historyResponse = await axios.get(`/api/patients/${this.patientDocument}/histories/latest`);
          if (historyResponse.data.success) {
            this.latestHistory = historyResponse.data.history;
          }
          
          // Cargar la evaluación de riesgo más reciente
          const assessmentResponse = await axios.get(`/api/patients/${this.patientDocument}/assessments/latest`);
          if (assessmentResponse.data.success) {
            this.latestAssessment = assessmentResponse.data.assessment;
          }
        } else {
          this.error = 'No se pudo cargar la información del paciente';
        }
      } catch (error) {
        console.error('Error al cargar datos del paciente:', error);
        this.error = 'Error al cargar datos del paciente';
      } finally {
        this.loading = false;
      }
    },
    async analyzeRisk() {
      this.isAnalyzing = true;
      
      try {
        const response = await axios.post('/api/risk/assessment', {
          patient_document: this.patientDocument
        });
        
        if (response.data.success) {
          this.latestAssessment = response.data.assessment;
          this.$emit('risk-assessment-created', response.data.assessment);
          this.$toasted.success('Análisis de riesgo completado');
        } else {
          this.$toasted.error('Error al analizar riesgo');
        }
      } catch (error) {
        console.error('Error al analizar riesgo:', error);
        this.$toasted.error('Error al analizar riesgo');
      } finally {
        this.isAnalyzing = false;
      }
    },
    async generateIntervention() {
      // Si ya tenemos una guía, solo mostrar el modal
      if (this.latestAssessment.interventionGuide) {
        this.interventionGuide = this.latestAssessment.interventionGuide;
        this.showInterventionModal = true;
        return;
      }
      
      this.loadingGuide = true;
      this.showInterventionModal = true;
      
      try {
        const response = await axios.post('/api/risk/assessment/intervention-guide', {
          assessment_id: this.latestAssessment.id
        });
        
        if (response.data.success) {
          this.interventionGuide = response.data.guide;
          // Actualizar la evaluación para incluir la nueva guía
          this.latestAssessment.interventionGuide = response.data.guide;
        } else {
          this.$toasted.error('Error al generar guía de intervención');
        }
      } catch (error) {
        console.error('Error al generar guía:', error);
        this.$toasted.error('Error al generar guía de intervención');
      } finally {
        this.loadingGuide = false;
      }
    },
    viewFullAssessment() {
      // Redireccionar a la página de detalle de la evaluación
      window.location.href = `/risk-assessment/${this.latestAssessment.id}`;
    },
    parseJsonArray(jsonString) {
      if (!jsonString) return [];
      try {
        return JSON.parse(jsonString);
      } catch (e) {
        console.error('Error parsing JSON:', e);
        return [];
      }
    },
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      return moment(dateString).format('DD/MM/YYYY');
    },
    truncateText(text, maxLength = 100) {
      if (!text) return 'No disponible';
      if (text.length <= maxLength) return text;
      return text.substring(0, maxLength) + '...';
    },
    toggleProblematics() {
      this.showProblematics = !this.showProblematics;
    },
    toggleObservations() {
      this.showObservations = !this.showObservations;
    },
    toggleRiskFactors() {
      this.showRiskFactors = !this.showRiskFactors;
    },
    toggleWarnings() {
      this.showWarnings = !this.showWarnings;
    },
    closeInterventionModal() {
      this.showInterventionModal = false;
    },
    printIntervention() {
      // Implementar funcionalidad de impresión
      window.print();
    }
  }
};
</script>

<style scoped>
.patient-info-container {
  height: 100%;
  overflow-y: auto;
  color: #333;
}

.loading-container, .error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 200px;
  color: #7f8c8d;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-radius: 50%;
  border-top: 4px solid #3498db;
  animation: spin 1s linear infinite;
  margin-bottom: 10px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-container i {
  font-size: 2rem;
  color: #e74c3c;
  margin-bottom: 10px;
}

.patient-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  border-bottom: 1px solid #eee;
  padding-bottom: 10px;
}

.patient-header h3 {
  margin: 0;
  color: #2c3e50;
}

.risk-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: bold;
}

.risk-high {
  background-color: #e74c3c;
  color: white;
}

.risk-medium {
  background-color: #f39c12;
  color: white;
}

.risk-low {
  background-color: #2ecc71;
  color: white;
}

.risk-unknown {
  background-color: #95a5a6;
  color: white;
}

.detail-group {
  margin-bottom: 10px;
  display: flex;
}

.detail-label {
  font-weight: bold;
  min-width: 90px;
  color: #7f8c8d;
}

.detail-value {
  flex: 1;
}

.clinical-info, .risk-assessment {
  margin-top: 20px;
  padding-top: 15px;
  border-top: 1px solid #eee;
}

.clinical-info h4, .risk-assessment h4 {
  margin-top: 0;
  margin-bottom: 15px;
  color: #2c3e50;
}

.expandable-section {
  margin: 10px 0;
}

.section-header {
  display: flex;
  align-items: center;
  cursor: pointer;
  padding: 8px;
  background-color: #f5f5f5;
  border-radius: 4px;
}

.section-header i {
  margin-right: 8px;
  color: #3498db;
}

.section-content {
  padding: 10px;
  background-color: #f9f9f9;
  border-radius: 0 0 4px 4px;
}

.section-content p {
  margin: 0;
  line-height: 1.5;
}

.section-content ul {
  padding-left: 20px;
  margin: 0;
}

.section-content li {
  margin-bottom: 5px;
}

.critical-sign {
  color: #e74c3c;
}

.critical-sign i {
  margin-right: 5px;
}

.risk-meter {
  display: flex;
  align-items: center;
  margin: 15px 0;
}

.risk-bar {
  flex: 1;
  height: 8px;
  background-color: #ecf0f1;
  border-radius: 4px;
  overflow: hidden;
  margin-right: 10px;
}

.risk-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.3s ease;
}

.risk-value {
  font-weight: bold;
  min-width: 45px;
  text-align: right;
}

.actions, .risk-actions {
  margin-top: 20px;
  display: flex;
  gap: 10px;
}

.action-btn, .analyze-btn {
  padding: 8px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 500;
  transition: background-color 0.2s;
}

.action-btn {
  background-color: #3498db;
  color: white;
}

.action-btn:hover {
  background-color: #2980b9;
}

.action-btn.secondary {
  background-color: #95a5a6;
}

.action-btn.secondary:hover {
  background-color: #7f8c8d;
}

.analyze-btn {
  background-color: #2ecc71;
  color: white;
}

.analyze-btn:hover {
  background-color: #27ae60;
}

.action-btn i, .analyze-btn i {
  margin-right: 5px;
}

.action-btn:disabled, .analyze-btn:disabled {
  background-color: #95a5a6;
  cursor: not-allowed;
}

/* Modal */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background-color: white;
  width: 80%;
  max-width: 800px;
  max-height: 90vh;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
}

.modal-header {
  padding: 15px 20px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  color: #2c3e50;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  color: #95a5a6;
}

.close-btn:hover {
  color: #e74c3c;
}

.modal-body {
  padding: 20px;
  overflow-y: auto;
  flex: 1;
}

.modal-footer {
  padding: 15px 20px;
  border-top: 1px solid #eee;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.guide-section {
  margin-bottom: 20px;
}

.guide-section h4 {
  margin-top: 0;
  margin-bottom: 10px;
  color: #2c3e50;
  border-bottom: 1px solid #eee;
  padding-bottom: 5px;
}

.guide-section ul, .guide-section ol {
  padding-left: 20px;
  margin: 0;
}

.guide-section li {
  margin-bottom: 8px;
  line-height: 1.5;
}

/* Responsive */
@media (max-width: 768px) {
  .modal-content {
    width: 95%;
  }
  
  .detail-group {
    flex-direction: column;
  }
  
  .detail-label {
    margin-bottom: 3px;
  }
}
</style>
