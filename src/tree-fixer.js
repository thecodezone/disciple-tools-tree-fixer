import {html, css, LitElement} from 'lit';
import {customElement, property, query} from 'lit/decorators.js';

@customElement('tree-fixer')
class TreeFixer extends LitElement {

  @property({type: Object})
  translations = {
      warning: 'Are you sure you want to run this? It can not be undone.',
      instructions: 'Fix errors in the tree by clicking the button below. Only run this if you know it is needed.',
      startLabel: 'Start',
      stopLabel: 'Stop',
      logHeading: 'Log',
      logCountText: 'records fixed.',
  }

  @property({type: String})
  endpoint = ''

  @property({type: Array})
  log = []

  @property({type: Boolean})
  running = false

  @property({type: Number})
  numberFixed = 0

  constructor() {
    super();
  }

  render() {
    const { translations, log, running, numberFixed } = this

    return html`
      <div>
        <p>
            ${translations.instructions}
        </p>
        <div class="buttons">
          ${running ? html`
            <dt-button context="alert" @click="${this.stop}">
              ${translations.stopLabel}
            </dt-button>
          ` : html`
            <dt-button context="primary" @click="${this.start}"">
              ${translations.startLabel}
            </dt-button>
          `}
          
        </div>
        
        ${log.length ? html`
          <h2>
            ${numberFixed} ${translations.logCountText}
          </h2>
          <table class="log widefat striped">
              <thead>
                <tr><th>${translations.logHeading}</th></tr>
              </thead>
              <tbody>
                ${log.map((item, i) => html`
                    <tr><td>${item}</td></tr>
                `)}
                </tbody>
          </table>
        ` : ''
        }
      </div>
    `
  }

  async start() {
    const { translations } = this
    const confirm = window.confirm(translations.warning)
    if (!confirm) {
        return;
    }
    this.running = true
    this.log = []
    this.numberFixed = 0
    this.fetch()
  }

  async fetch() {
    if (!this.running) {
       return;
    }
    const { translations, endpoint } = this
    const nonce = window.dt_admin_scripts.nonce
    const headers = {
      'Content-Type': 'application/json; charset=utf-8',
      'X-WP-Nonce': nonce
    }
    let result

    try {
      result = await fetch(
          endpoint,
          {
            method: 'POST',
            headers,
          }
      )
    } catch (error) {
      this.handleError(error.message)
      return;
    }

    if (!result.ok) {
      this.handleError(result.statusText ? result.statusText : translations.fetchError)
      return;
    }

    const data = await result.json()
    this.handleSuccess(data)
  }

  stop() {
    this.running = false
  }

  handleError(message) {
      const { log } = this
      this.log = [...log, message]
      this.stop()
  }

  handleSuccess(data) {
      let { numberFixed, running, log } = this
      if (data.record) {
          this.numberFixed = numberFixed + 1;
      }
      this.log = [...log, ...data.log]
      if (!data.continue) {
          this.running = false;
      }
      if (!this.running) {
        return;
      }
      window.setTimeout(this.fetch.bind(this), 1000)
  }

  static get styles() {
    return css`
      :host {
        max-width: 800px;
        margin: 0 auto;
=       text-align: left;
      }

      .logo {
        height: 6em;
        padding: 1.5em;
        will-change: filter;
        transition: filter 300ms;
      }
      .logo:hover {
        filter: drop-shadow(0 0 2em #646cffaa);
      }
      .logo.lit:hover {
        filter: drop-shadow(0 0 2em #325cffaa);
      }

      .card {
        padding: 2em;
      }

      .read-the-docs {
        color: #888;
      }

      a {
        font-weight: 500;
        color: #646cff;
        text-decoration: inherit;
      }
      a:hover {
        color: #535bf2;
      }

      ::slotted(h1) {
        font-size: 3.2em;
        line-height: 1.1;
      }

      button {
        border-radius: 8px;
        border: 1px solid transparent;
        padding: 0.6em 1.2em;
        font-size: 1em;
        font-weight: 500;
        font-family: inherit;
        background-color: #1a1a1a;
        cursor: pointer;
        transition: border-color 0.25s;
      }
      button:hover {
        border-color: #646cff;
      }
      button:focus,
      button:focus-visible {
        outline: 4px auto -webkit-focus-ring-color;
      }
      
      table.widefat {
        width: 100%;
        background: #fff;
        border: 1px solid #c3c4c7;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
      }

      .widefat th,
      .widefat td {
        padding: 8px 10px;
      }
      
      .widefat th {
        font-weight: bold;
      }

      .widefat tfoot tr td, .widefat tfoot tr th, .widefat thead tr td, .widefat thead tr th {
        color: #2c3338;
      }

      .widefat thead td, .widefat thead th, .widget .widget-top {
        line-height: 1.4em;
      }

      .widefat tfoot td, .widefat th, .widefat thead td {
        font-weight: 400;
      }

      .widefat thead td, .widefat thead th {
        border-bottom: 1px solid #c3c4c7;
      }
      

      element.style {
      }
      
      .widefat tfoot tr td, .widefat tfoot tr th, .widefat thead tr td, .widefat thead tr th {
        color: #2c3338;
      }
      #nav-menu-footer, #nav-menu-header, #your-profile #rich_editing, .checkbox, .control-section .accordion-section-title, .menu-item-handle, .postbox .hndle, .side-info, .sidebar-name, .stuffbox .hndle, .widefat tfoot td, .widefat tfoot th, .widefat thead td, .widefat thead th, .widget .widget-top {
        line-height: 1.4em;
      }
      .widefat thead td, .widefat thead th {
        border-bottom: 1px solid #c3c4c7;
      }
      .widefat tfoot td, .widefat th, .widefat thead td {
        font-weight: 400;
      }
      .widefat td, .widefat th {
        color: #50575e;
      }
      .widefat tfoot td, .widefat th, .widefat thead td {
        text-align: left;
        line-height: 1.3em;
        font-size: 14px;
      }

      .widefat * {
        word-wrap: break-word;
      }

      .alternate, .striped>tbody>:nth-child(odd), ul.striped>:nth-child(odd) {
        background-color: #f6f7f7;
      }

      .widefat tfoot td, .widefat th, .widefat thead td {
        font-weight: 400;
      }

      @media (prefers-color-scheme: light) {
        a:hover {
          color: #747bff;
        }
        button {
          background-color: #f9f9f9;
        }
      }
    `
  }
}
