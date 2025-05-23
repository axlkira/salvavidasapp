
<template>
  <div class="chat-container">
    <div class="chat-sidebar">
      <div class="provider-selector">
        <h3>Proveedor de IA</h3>
        <select v-model="selectedProvider" @change="onProviderChange" class="provider-select">
          <option v-for="(info, provider) in providers" :key="provider" :value="provider">
            {{ info.name }} - {{ info.model }}
          </option>
        </select>
        <div class="privacy-indicator" :class="getPrivacyLevelClass">
          <i class="fas" :class="getPrivacyIcon"></i>
          <span>{{ getPrivacyLevel }}</span>
        </div>
      </div>

      <div class="conversation-list">
        <h3>Conversaciones</h3>
        <div class="search-bar">
          <input type="text" v-model="searchTerm" placeholder="Buscar conversación..." class="search-input">
          <button @click="createNewConversation" class="new-chat-btn">
            <i class="fas fa-plus"></i> Nueva
          </button>
        </div>
        <div class="conversations">
          <div 
            v-for="conversation in filteredConversations" 
            :key="conversation.id" 
            @click="selectConversation(conversation)" 
            class="conversation-item"
            :class="{ active: currentConversation && conversation.id === currentConversation.id }">
            <div class="conversation-title">{{ conversation.title || 'Nueva conversación' }}</div>
            <div class="conversation-meta">
              <span class="timestamp">{{ formatDate(conversation.updated_at) }}</span>
              <span v-if="conversation.patient_document" class="patient-tag">
                <i class="fas fa-user-injured"></i>
              </span>
            </div>
          </div>
          <div v-if="conversations.length === 0" class="no-conversations">
            No hay conversaciones. Crea una nueva para comenzar.
          </div>
        </div>
      </div>

      <div class="patient-selector" v-if="showPatientSelector">
        <h3>Seleccionar Paciente (Opcional)</h3>
        <div class="search-bar">
          <input type="text" v-model="patientSearchTerm" placeholder="Buscar por documento..." class="search-input">
          <button @click="searchPatient" class="search-btn">
            <i class="fas fa-search"></i>
          </button>
        </div>
        <div v-if="patientSearchResults.length > 0" class="patient-results">
          <div 
            v-for="patient in patientSearchResults" 
            :key="patient.documento" 
            @click="selectPatient(patient)" 
            class="patient-item">
            <div class="patient-name">{{ patient.nombre_completo }}</div>
            <div class="patient-doc">{{ patient.documento }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="chat-main">
      <div class="chat-header">
        <h2>{{ currentConversation ? currentConversation.title : 'Nueva conversación' }}</h2>
        <div class="chat-actions">
          <button v-if="currentConversation" @click="analyzeRisk" class="analyze-btn" :disabled="isAnalyzing">
            <i class="fas fa-chart-bar"></i> Analizar Riesgo
          </button>
          <button v-if="currentConversation" @click="deleteConversation" class="delete-btn">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </div>

      <div ref="messagesContainer" class="chat-messages">
        <div v-if="!currentConversation" class="welcome-message">
          <div class="welcome-icon">
            <i class="fas fa-comments"></i>
          </div>
          <h3>Bienvenido a SalvaVidas AI</h3>
          <p>Selecciona una conversación existente o crea una nueva para comenzar a chatear.</p>
        </div>
        <template v-else>
          <div 
            v-for="(message, index) in currentMessages" 
            :key="index" 
            class="message-container"
            :class="message.role">
            <div class="message-avatar">
              <i class="fas" :class="getAvatarIcon(message.role)"></i>
            </div>
            <div class="message-content">
              <div class="message-text" v-html="formatMessage(message.content)"></div>
              <div class="message-time">{{ formatTime(message.created_at) }}</div>
            </div>
          </div>
          <div v-if="isTyping" class="message-container assistant typing">
            <div class="message-avatar">
              <i class="fas fa-robot"></i>
            </div>
            <div class="message-content">
              <div class="typing-indicator">
                <span></span>
                <span></span>
                <span></span>
              </div>
            </div>
          </div>
        </template>
      </div>

      <div class="chat-input" v-if="currentConversation">
        <textarea 
          ref="messageInput"
          v-model="newMessage" 
          placeholder="Escribe tu mensaje..." 
          @keydown.enter.prevent="sendMessage"
          :disabled="isTyping"></textarea>
        <button @click="sendMessage" :disabled="!newMessage.trim() || isTyping" class="send-btn">
          <i class="fas fa-paper-plane"></i>
        </button>
      </div>
    </div>

    <div class="info-panel" v-if="currentConversation && currentConversation.patient_document">
      <patient-info 
        :patient-document="currentConversation.patient_document"
        @risk-assessment-created="onRiskAssessmentCreated">
      </patient-info>
    </div>
  </div>
</template>

<script>
import PatientInfo from './PatientInfo.vue';
import { marked } from 'marked';
import axios from 'axios';
import moment from 'moment';
import 'moment/locale/es';

export default {
  components: {
    PatientInfo
  },
  data() {
    return {
      conversations: [],
      currentConversation: null,
      currentMessages: [],
      newMessage: '',
      isTyping: false,
      isAnalyzing: false,
      searchTerm: '',
      patientSearchTerm: '',
      patientSearchResults: [],
      showPatientSelector: false,
      selectedProvider: 'ollama',
      providers: {
        ollama: { 
          name: 'Ollama (Local)', 
          model: 'qwen3:8b',
          privacy_level: 'alto'
        }
      }
    }
  },
  computed: {
    filteredConversations() {
      if (!this.searchTerm) return this.conversations;
      
      const term = this.searchTerm.toLowerCase();
      return this.conversations.filter(conv => 
        (conv.title && conv.title.toLowerCase().includes(term))
      );
    },
    getPrivacyLevel() {
      const provider = this.providers[this.selectedProvider];
      return provider ? `Privacidad: ${provider.privacy_level || 'Desconocida'}` : 'Privacidad: Desconocida';
    },
    getPrivacyLevelClass() {
      const provider = this.providers[this.selectedProvider];
      if (!provider || !provider.privacy_level) return 'privacy-unknown';
      
      const level = provider.privacy_level.toLowerCase();
      if (level.includes('alto')) return 'privacy-high';
      if (level.includes('medio')) return 'privacy-medium';
      if (level.includes('bajo')) return 'privacy-low';
      return 'privacy-unknown';
    },
    getPrivacyIcon() {
      const provider = this.providers[this.selectedProvider];
      if (!provider || !provider.privacy_level) return 'fa-question-circle';
      
      const level = provider.privacy_level.toLowerCase();
      if (level.includes('alto')) return 'fa-lock';
      if (level.includes('medio')) return 'fa-lock-open';
      if (level.includes('bajo')) return 'fa-exclamation-triangle';
      return 'fa-question-circle';
    }
  },
  mounted() {
    this.loadConversations();
    this.loadProviders();
    moment.locale('es');
  },
  methods: {
    async loadConversations() {
      try {
        const response = await axios.get('/api/chat/conversations');
        if (response.data.success) {
          this.conversations = response.data.conversations;
        }
      } catch (error) {
        console.error('Error al cargar conversaciones:', error);
        this.$toasted.error('Error al cargar conversaciones');
      }
    },
    async loadProviders() {
      try {
        const response = await axios.get('/api/chat/providers');
        if (response.data.success) {
          this.providers = response.data.providers;
          // Asegurarse de que al menos Ollama esté disponible
          if (!this.providers.ollama) {
            this.providers.ollama = { 
              name: 'Ollama (Local)', 
              model: 'qwen3:8b',
              privacy_level: 'alto'
            };
          }
        }
      } catch (error) {
        console.error('Error al cargar proveedores:', error);
        // Mantener el proveedor Ollama por defecto
      }
    },
    async selectConversation(conversation) {
      this.currentConversation = conversation;
      this.showPatientSelector = false;
      
      try {
        const response = await axios.get(`/api/chat/conversation/${conversation.id}`);
        if (response.data.success) {
          this.currentMessages = response.data.conversation.messages;
          this.scrollToBottom();
        }
      } catch (error) {
        console.error('Error al cargar mensajes:', error);
        this.$toasted.error('Error al cargar mensajes');
      }
    },
    async createNewConversation() {
      this.currentConversation = null;
      this.currentMessages = [];
      this.showPatientSelector = true;
    },
    async sendMessage() {
      if (!this.newMessage.trim() || this.isTyping) return;
      
      // Si no hay conversación activa, crear una nueva
      if (!this.currentConversation) {
        await this.createConversationAndSendMessage();
        return;
      }
      
      const messageContent = this.newMessage;
      this.newMessage = '';
      
      // Añadir mensaje del usuario a la interfaz
      this.currentMessages.push({
        role: 'user',
        content: messageContent,
        created_at: new Date().toISOString()
      });
      
      this.scrollToBottom();
      this.isTyping = true;
      
      try {
        const response = await axios.post('/api/chat/message', {
          conversation_id: this.currentConversation.id,
          message: messageContent,
          provider: this.selectedProvider
        });
        
        if (response.data.success) {
          // Reemplazar mensajes temporales con los reales
          this.currentMessages = response.data.conversation.messages;
          this.scrollToBottom();
        } else {
          this.$toasted.error('Error al enviar mensaje');
        }
      } catch (error) {
        console.error('Error al enviar mensaje:', error);
        this.$toasted.error('Error al enviar mensaje');
        
        // Añadir mensaje de error
        this.currentMessages.push({
          role: 'assistant',
          content: 'Lo siento, ha ocurrido un error al procesar tu mensaje. Por favor, intenta de nuevo.',
          created_at: new Date().toISOString()
        });
      } finally {
        this.isTyping = false;
        this.scrollToBottom();
      }
    },
    async createConversationAndSendMessage() {
      const payload = {
        title: 'Nueva conversación',
        provider: this.selectedProvider
      };
      
      // Si hay un paciente seleccionado, añadirlo
      if (this.selectedPatient) {
        payload.patient_document = this.selectedPatient.documento;
      }
      
      try {
        const response = await axios.post('/api/chat/conversation', payload);
        
        if (response.data.success) {
          this.currentConversation = response.data.conversation;
          this.conversations.unshift(this.currentConversation);
          this.showPatientSelector = false;
          
          // Ahora enviar el mensaje
          await this.sendMessage();
        } else {
          this.$toasted.error('Error al crear conversación');
        }
      } catch (error) {
        console.error('Error al crear conversación:', error);
        this.$toasted.error('Error al crear conversación');
      }
    },
    async deleteConversation() {
      if (!this.currentConversation) return;
      
      if (!confirm('¿Estás seguro de que deseas eliminar esta conversación?')) return;
      
      try {
        const response = await axios.delete(`/api/chat/conversation/${this.currentConversation.id}`);
        
        if (response.data.success) {
          const index = this.conversations.findIndex(c => c.id === this.currentConversation.id);
          if (index !== -1) {
            this.conversations.splice(index, 1);
          }
          
          this.currentConversation = null;
          this.currentMessages = [];
          this.$toasted.success('Conversación eliminada');
        } else {
          this.$toasted.error('Error al eliminar conversación');
        }
      } catch (error) {
        console.error('Error al eliminar conversación:', error);
        this.$toasted.error('Error al eliminar conversación');
      }
    },
    async analyzeRisk() {
      if (!this.currentConversation || !this.currentConversation.patient_document) {
        this.$toasted.error('Se requiere un paciente para analizar riesgo');
        return;
      }
      
      this.isAnalyzing = true;
      
      try {
        const response = await axios.post('/api/risk/assessment', {
          patient_document: this.currentConversation.patient_document,
          conversation_id: this.currentConversation.id,
          provider: this.selectedProvider
        });
        
        if (response.data.success) {
          this.$toasted.success('Análisis de riesgo completado');
          this.$emit('risk-assessment-created', response.data.assessment);
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
    async searchPatient() {
      if (!this.patientSearchTerm.trim()) return;
      
      try {
        // Endpoint hipotético para buscar pacientes
        const response = await axios.get(`/api/patients/search?term=${this.patientSearchTerm}`);
        
        if (response.data.success) {
          this.patientSearchResults = response.data.patients;
        } else {
          this.$toasted.error('Error al buscar pacientes');
        }
      } catch (error) {
        console.error('Error al buscar pacientes:', error);
        this.$toasted.error('Error al buscar pacientes');
      }
    },
    selectPatient(patient) {
      this.selectedPatient = patient;
      this.showPatientSelector = false;
      this.createConversationAndSendMessage();
    },
    onProviderChange() {
      // Si cambia a un proveedor externo, mostrar advertencia
      const provider = this.providers[this.selectedProvider];
      if (provider && provider.privacy_level && !provider.privacy_level.toLowerCase().includes('alto')) {
        alert('ADVERTENCIA: Has seleccionado un proveedor externo. La información sensible de los pacientes podría ser enviada a servidores externos. Asegúrate de que esto cumple con tus políticas de privacidad.');
      }
    },
    formatMessage(content) {
      if (!content) return '';
      // Convertir markdown a HTML
      return marked(content);
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const date = moment(dateString);
      if (moment().diff(date, 'days') < 1) {
        return date.format('HH:mm');
      } else if (moment().diff(date, 'days') < 7) {
        return date.format('ddd');
      } else {
        return date.format('DD/MM/YYYY');
      }
    },
    formatTime(dateString) {
      if (!dateString) return '';
      return moment(dateString).format('HH:mm');
    },
    getAvatarIcon(role) {
      switch (role) {
        case 'user': return 'fa-user';
        case 'assistant': return 'fa-robot';
        case 'system': return 'fa-cog';
        default: return 'fa-comment';
      }
    },
    scrollToBottom() {
      this.$nextTick(() => {
        if (this.$refs.messagesContainer) {
          this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
        }
      });
    },
    onRiskAssessmentCreated(assessment) {
      // Informar al componente padre que se ha creado una evaluación
      this.$emit('risk-assessment-created', assessment);
    }
  }
}
</script>

<style scoped>
.chat-container {
  display: flex;
  height: calc(100vh - 100px);
  background-color: #f8f9fa;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
}

.chat-sidebar {
  width: 300px;
  background-color: #2c3e50;
  color: #ecf0f1;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

.provider-selector {
  padding: 15px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.provider-select {
  width: 100%;
  padding: 8px;
  background-color: #34495e;
  color: white;
  border: none;
  border-radius: 4px;
  margin-top: 5px;
}

.privacy-indicator {
  margin-top: 10px;
  font-size: 0.8em;
  display: flex;
  align-items: center;
  gap: 5px;
}

.privacy-high {
  color: #2ecc71;
}

.privacy-medium {
  color: #f39c12;
}

.privacy-low {
  color: #e74c3c;
}

.privacy-unknown {
  color: #95a5a6;
}

.conversation-list {
  flex: 1;
  padding: 15px;
}

.search-bar {
  display: flex;
  margin-bottom: 15px;
}

.search-input {
  flex: 1;
  padding: 8px;
  border: none;
  border-radius: 4px 0 0 4px;
  background-color: #34495e;
  color: white;
}

.search-input::placeholder {
  color: #95a5a6;
}

.new-chat-btn, .search-btn {
  padding: 8px 12px;
  background-color: #3498db;
  color: white;
  border: none;
  border-radius: 0 4px 4px 0;
  cursor: pointer;
}

.new-chat-btn:hover, .search-btn:hover {
  background-color: #2980b9;
}

.conversations {
  margin-top: 10px;
  max-height: 300px;
  overflow-y: auto;
}

.conversation-item {
  padding: 10px;
  border-radius: 4px;
  margin-bottom: 5px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.conversation-item:hover {
  background-color: #34495e;
}

.conversation-item.active {
  background-color: #3498db;
}

.conversation-title {
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.conversation-meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.8em;
  color: #bdc3c7;
  margin-top: 3px;
}

.no-conversations {
  color: #95a5a6;
  text-align: center;
  margin-top: 20px;
}

.patient-selector {
  padding: 15px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.patient-results {
  margin-top: 10px;
  max-height: 200px;
  overflow-y: auto;
}

.patient-item {
  padding: 10px;
  border-radius: 4px;
  margin-bottom: 5px;
  cursor: pointer;
  background-color: #34495e;
}

.patient-item:hover {
  background-color: #3498db;
}

.patient-name {
  font-weight: 500;
}

.patient-doc {
  font-size: 0.8em;
  color: #bdc3c7;
}

.chat-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  background-color: white;
}

.chat-header {
  padding: 15px;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.chat-actions {
  display: flex;
  gap: 10px;
}

.analyze-btn, .delete-btn {
  padding: 8px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.analyze-btn {
  background-color: #2ecc71;
  color: white;
}

.analyze-btn:hover {
  background-color: #27ae60;
}

.analyze-btn:disabled {
  background-color: #95a5a6;
  cursor: not-allowed;
}

.delete-btn {
  background-color: #e74c3c;
  color: white;
}

.delete-btn:hover {
  background-color: #c0392b;
}

.chat-messages {
  flex: 1;
  padding: 15px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}

.welcome-message {
  margin: auto;
  text-align: center;
  color: #7f8c8d;
  max-width: 400px;
}

.welcome-icon {
  font-size: 3em;
  margin-bottom: 15px;
  color: #3498db;
}

.message-container {
  display: flex;
  margin-bottom: 15px;
  max-width: 80%;
}

.message-container.user {
  align-self: flex-end;
  flex-direction: row-reverse;
}

.message-container.assistant, .message-container.system {
  align-self: flex-start;
}

.message-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background-color: #3498db;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 10px;
}

.message-container.user .message-avatar {
  background-color: #2ecc71;
}

.message-container.system .message-avatar {
  background-color: #95a5a6;
}

.message-content {
  background-color: #f1f0f0;
  padding: 10px 15px;
  border-radius: 18px;
  position: relative;
}

.message-container.user .message-content {
  background-color: #3498db;
  color: white;
}

.message-container.system .message-content {
  background-color: #ecf0f1;
  font-style: italic;
}

.message-text {
  line-height: 1.5;
}

.message-text p {
  margin: 0 0 10px 0;
}

.message-text p:last-child {
  margin-bottom: 0;
}

.message-time {
  font-size: 0.7em;
  color: #95a5a6;
  margin-top: 5px;
  text-align: right;
}

.message-container.user .message-time {
  color: rgba(255, 255, 255, 0.8);
}

.typing-indicator {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 20px;
}

.typing-indicator span {
  height: 8px;
  width: 8px;
  margin: 0 2px;
  background-color: #bdc3c7;
  border-radius: 50%;
  display: inline-block;
  animation: bounce 1.5s infinite ease-in-out;
}

.typing-indicator span:nth-child(2) {
  animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes bounce {
  0%, 60%, 100% {
    transform: translateY(0);
  }
  30% {
    transform: translateY(-5px);
  }
}

.chat-input {
  padding: 15px;
  border-top: 1px solid #e0e0e0;
  display: flex;
}

.chat-input textarea {
  flex: 1;
  padding: 12px;
  border: 1px solid #e0e0e0;
  border-radius: 20px;
  resize: none;
  height: 50px;
  outline: none;
  font-family: inherit;
}

.send-btn {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background-color: #3498db;
  color: white;
  border: none;
  margin-left: 10px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.send-btn:hover {
  background-color: #2980b9;
}

.send-btn:disabled {
  background-color: #95a5a6;
  cursor: not-allowed;
}

.info-panel {
  width: 300px;
  background-color: #ecf0f1;
  padding: 15px;
  overflow-y: auto;
}

/* Responsive */
@media (max-width: 1200px) {
  .info-panel {
    display: none;
  }
}

@media (max-width: 768px) {
  .chat-container {
    flex-direction: column;
    height: calc(100vh - 60px);
  }
  
  .chat-sidebar {
    width: 100%;
    height: 200px;
    overflow-y: auto;
  }
  
  .message-container {
    max-width: 90%;
  }
}
</style>