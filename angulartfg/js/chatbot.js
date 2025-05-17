document.addEventListener("DOMContentLoaded", function () {
    const messages = document.getElementById("chatbot-messages");
    const input = document.getElementById("chatbot-input");
    const sendBtn = document.getElementById("chatbot-send");

    sendBtn.addEventListener("click", sendMessage);
    input.addEventListener("keypress", e => {
        if (e.key === "Enter") sendMessage();
    });

    appendMessage("¡Hola! ¿En qué puedo ayudarte hoy?\nOpciones:\n1. Buscar coche\n2. Reportar un problema\n3. Otra consulta", "bot");

    let state = { step: "init", priceFrom: null, priceTo: null };

    function sendMessage() {
        const msg = input.value.trim();
        if (!msg) return;
        appendMessage(msg, "user");
        input.value = "";

        fetch("chatbot.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `mensaje=${encodeURIComponent(msg)}&state=${encodeURIComponent(JSON.stringify(state))}`
        })
        .then(res => res.json())
        .then(data => {
            state = data.state;
            appendMessage(data.respuesta, "bot");
        });
    }

    function appendMessage(text, type) {
        const div = document.createElement("div");
        div.className = `chatbot-message chatbot-${type}`;
        div.innerText = text;
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
    }
});
