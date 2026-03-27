# 🚀 LobbyTP

Un plugin dinámico para **PocketMine-MP (API 2.0.0)** que permite teletransportar a los jugadores al **Lobby** con un sistema de cuenta regresiva, efectos visuales y sonidos inmersivos. Ideal para servidores con zonas de espera o hubs centrales que requieren orden y estilo.

---

### ⚡ Función
*   **Teletransporte Seguro:** Inicia una cuenta regresiva (configurable) antes de mover al jugador; si el jugador se mueve, el proceso se cancela automáticamente.
*   **Efectos Inmersivos:**
    *   Aplica ceguera temporal durante la espera para evitar trampas visuales.
    *   Reproduce un sonido de **Blaze Shoot** cada segundo durante la cuenta atrás.
    *   Muestra un **Popup** con el tiempo restante (`{time}s`).
*   **Comando Hub Alternativo:** Incluye `/hub` que elimina al jugador instantáneamente y lo reenvía al spawn del lobby (útil para reseteos rápidos).
*   **Mensajes Personalizables:** Notificaciones de éxito, cancelación y advertencias editables con prefijos y colores.

---

### ⚙️ Configuración Rápida
Edita el `config.yml` para ajustar:
*   `teleport_time`: Segundos de espera antes del teletransporte (ej. 5).
*   `prefix`: Prefijo visual para todos los mensajes del plugin.
*   `dont_move_message`: Aviso que aparece si el jugador intenta caminar durante la espera.
*   `teleport_popup_message`: Plantilla del contador flotante (usa `{time}`).
*   `hub_message`: Mensaje al usar el comando `/hub`.

> **Autor:** DarkoMC  
> **Versión:** 1.0.0  
> **Requisito:** Ninguno (funciona nativo). ¡Solo instala y reinicia!
