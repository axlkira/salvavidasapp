<template>
  <div class="risk-list-container">
    <div class="list-header">
      <h2>Evaluaciones de Riesgo</h2>
      <div class="filter-controls">
        <div class="filter-group">
          <label>Nivel de Riesgo:</label>
          <select v-model="filters.riskLevel" class="filter-select">
            <option value="">Todos</option>
            <option value="bajo">Bajo</option>
            <option value="moderado">Moderado</option>
            <option value="alto">Alto</option>
            <option value="critico">Crítico</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Estado:</label>
          <select v-model="filters.status" class="filter-select">
            <option value="">Todos</option>
            <option value="pending">Pendientes</option>
            <option value="reviewed">Revisados</option>
            <option value="archived">Archivados</option>
          </select>
        </div>
        <div class="search-group">
          <input 
            type="text" 
            v-model="filters.search" 
            placeholder="Buscar por nombre o documento..." 
            class="search-input"
            @input="debounceSearch">
          <button @click="loadAssessments" class="search-btn">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Cargando evaluaciones...</p>
    </div>

    <div v-else-if="error" class="error-container">
      <i class="fas fa-exclamation-circle"></i>
      <p>{{ error }}</p>
      <button @click="loadAssessments" class="retry-btn">Reintentar</button>
    </div>

    <div v-else-if="assessments.length === 0" class="empty-state">
      <i class="fas fa-clipboard-list"></i>
      <p>No se encontraron evaluaciones de riesgo con los filtros actuales.</p>
      <button @click="resetFilters" class="reset-btn">Limpiar Filtros</button>
    </div>

    <div v-else class="assessment-list">
      <div 
        v-for="assessment in assessments" 
        :key="assessment.id" 
        class="assessment-card"
        @click="viewAssessment(assessment.id)">
        <div class="risk-indicator" :class="getRiskLevelClass(assessment.risk_level)"></div>
        <div class="assessment-content">
          <div class="assessment-header">
            <h3 class="patient-name">{{ getPatientName(assessment) }}</h3>
            <div class="status-badge" :class="getStatusClass(assessment.status)">
              {{ getStatusText(assessment.status) }}
            </div>
          </div>
          <div class="assessment-details">
            <div class="detail-item">
              <i class="fas fa-user-tag"></i>
              <span>{{ assessment.patient?.documento || 'Sin documento' }}</span>
            </div>
            <div class="detail-item">
              <i class="fas fa-calendar-alt"></i>
              <span>{{ formatDate(assessment.created_at) }}</span>
            </div>
            <div class="detail-item">
              <i class="fas fa-chart-line"></i>
              <span>Riesgo: {{ assessment.risk_level }} ({{ (assessment.risk_score * 100).toFixed(0) }}%)</span>
            </div>
          </div>
          <div class="assessment-summary">
            <div class="summary-item">
              <div class="summary-label">Factores de Riesgo:</div>
              <div class="summary-value">{{ assessment.riskFactors?.length || 0 }}</div>
            </div>
            <div class="summary-item">
              <div class="summary-label">Señales de Alerta:</div>
              <div class="summary-value">{{ assessment.warningSigns?.length || 0 }}</div>
            </div>
            <div class="summary-item">
              <div class="summary-label">Guía de Intervención:</div>
              <div class="summary-value">
                <i class="fas" :class="assessment.interventionGuide ? 'fa-check text-success' : 'fa-times text-danger'"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="pagination.total > pagination.perPage" class="pagination">
        <button 
          @click="changePage(pagination.currentPage - 1)" 
          :disabled="pagination.currentPage === 1"
          class="page-btn">
          <i class="fas fa-chevron-left"></i>
        </button>
        <span class="page-info">
          Página {{ pagination.currentPage }} de {{ pagination.totalPages }}
        </span>
        <button 
          @click="changePage(pagination.currentPage + 1)" 
          :disabled="pagination.currentPage === pagination.totalPages"
          class="page-btn">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import moment from 'moment';
import { debounce } from 'lodash';

export default {
  data() {
    return {
      assessments: [],
      loading: true,
      error: null,
      filters: {
        riskLevel: '',
        status: '',
        search: '',
      },
      pagination: {
        currentPage: 1,
        perPage: 10,
        total: 0,
        totalPages: 1
      },
      debounceSearch: debounce(function() {
        this.pagination.currentPage = 1;
        this.loadAssessments();
      }, 500)
    };
  },
  mounted() {
    this.loadAssessments();
  },
  methods: {
    async loadAssessments() {
      this.loading = true;
      this.error = null;
      
      try {
        // Construir parámetros de consulta
        const params = {
          page: this.pagination.currentPage,
          per_page: this.pagination.perPage
        };
        
        if (this.filters.riskLevel) {
          params.risk_level = this.filters.riskLevel;
        }
        
        if (this.filters.status) {
          params.status = this.filters.status;
        }
        
        if (this.filters.search) {
          params.search = this.filters.search;
        }
        
        const response = await axios.get('/api/risk/assessments', { params });
        
        if (response.data.success) {
          this.assessments = response.data.assessments.data;
          this.pagination.total = response.data.assessments.total;
          this.pagination.currentPage = response.data.assessments.current_page;
          this.pagination.totalPages = response.data.assessments.last_page;
        } else {
          this.error = 'No se pudieron cargar las evaluaciones de riesgo';
        }
      } catch (error) {
        console.error('Error loading risk assessments:', error);
        this.error = 'Error al cargar las evaluaciones: ' + (error.response?.data?.message || error.message);
      } finally {
        this.loading = false;
      }
    },
    
    changePage(page) {
      if (page < 1 || page > this.pagination.totalPages) return;
      this.pagination.currentPage = page;
      this.loadAssessments();
    },
    
    resetFilters() {
      this.filters = {
        riskLevel: '',
        status: '',
        search: ''
      };
      this.pagination.currentPage = 1;
      this.loadAssessments();
    },
    
    viewAssessment(id) {
      window.location.href = `/risk-assessment/${id}`;
    },
    
    getRiskLevelClass(riskLevel) {
      if (!riskLevel) return 'risk-unknown';
      
      const level = riskLevel.toLowerCase();
      if (level.includes('bajo')) return 'risk-low';
      if (level.includes('moderado')) return 'risk-medium';
      if (level.includes('alto')) return 'risk-high';
      if (level.includes('crítico') || level.includes('critico')) return 'risk-critical';
      return 'risk-unknown';
    },
    
    getStatusClass(status) {
      switch (status) {
        case 'pending': return 'status-pending';
        case 'reviewed': return 'status-reviewed';
        case 'archived': return 'status-archived';
        default: return '';
      }
    },
    
    getStatusText(status) {
      switch (status) {
        case 'pending': return 'Pendiente';
        case 'reviewed': return 'Revisado';
        case 'archived': return 'Archivado';
        default: return 'Desconocido';
      }
    },
    
    getPatientName(assessment) {
      if (assessment.patient && assessment.patient.nombre_completo) {
        return assessment.patient.nombre_completo;
      }
      return `Paciente ${assessment.patient_document || 'Sin Identificar'}`;
    },
    
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      return moment(dateString).format('DD/MM/YYYY HH:mm');
    }
  },
  watch: {
    'filters.riskLevel'() {
      this.pagination.currentPage = 1;
      this.loadAssessments();
    },
    'filters.status'() {
      this.pagination.currentPage = 1;
      this.loadAssessments();
    }
  }
};
</script>

<style scoped>
.risk-list-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.list-header {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  gap: 15px;
}

.list-header h2 {
  margin: 0;
  color: #2c3e50;
  font-size: 1.8rem;
}

.filter-controls {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 15px;
}

.filter-group, .search-group {
  display: flex;
  align-items: center;
  gap: 8px;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #dfe4ea;
  border-radius: 4px;
  background-color: white;
  min-width: 120px;
}

.search-input {
  padding: 8px 12px;
  border: 1px solid #dfe4ea;
  border-radius: 4px;
  min-width: 200px;
}

.search-btn {
  padding: 8px 12px;
  background-color: #3498db;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.loading-container, .error-container, .empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 200px;
  text-align: center;
  padding: 30px;
  background-color: #f8f9fa;
  border-radius: 8px;
  color: #7f8c8d;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-radius: 50%;
  border-top: 4px solid #3498db;
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

.error-container i, .empty-state i {
  font-size: 3rem;
  margin-bottom: 15px;
}

.retry-btn, .reset-btn {
  margin-top: 15px;
  padding: 8px 16px;
  background-color: #3498db;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.assessment-list {
  display: grid;
  grid-gap: 20px;
}

.assessment-card {
  display: flex;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.assessment-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.risk-indicator {
  width: 8px;
  flex-shrink: 0;
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

.assessment-content {
  flex: 1;
  padding: 15px;
}

.assessment-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.patient-name {
  margin: 0;
  font-size: 1.2rem;
  color: #2c3e50;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 20px;
  font-size: 0.8rem;
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

.assessment-details {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  margin-bottom: 15px;
  font-size: 0.9rem;
  color: #7f8c8d;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 5px;
}

.assessment-summary {
  display: flex;
  border-top: 1px solid #ecf0f1;
  padding-top: 10px;
}

.summary-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 0 10px;
}

.summary-label {
  font-size: 0.8rem;
  color: #7f8c8d;
  margin-bottom: 5px;
}

.summary-value {
  font-weight: bold;
  color: #2c3e50;
}

.text-success {
  color: #2ecc71;
}

.text-danger {
  color: #e74c3c;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
  gap: 10px;
}

.page-btn {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: white;
  border: 1px solid #dfe4ea;
  border-radius: 50%;
  cursor: pointer;
  transition: background-color 0.2s;
}

.page-btn:hover:not(:disabled) {
  background-color: #f1f2f6;
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-size: 0.9rem;
  color: #7f8c8d;
}

@media (max-width: 768px) {
  .list-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .filter-controls {
    width: 100%;
    flex-direction: column;
    align-items: stretch;
  }
  
  .filter-group, .search-group {
    width: 100%;
  }
  
  .assessment-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
  }
  
  .status-badge {
    align-self: flex-start;
  }
}
</style>
