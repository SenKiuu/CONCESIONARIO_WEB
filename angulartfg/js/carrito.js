document.addEventListener('DOMContentLoaded', function() {
    // Cargar carrito desde localStorage
    let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    actualizarContadorCarrito();
    
    // Función para agregar al carrito
    window.agregarCarrito = function(idCoche) {
        // Obtener datos del coche desde el DOM
        const card = document.querySelector(`[onclick="agregarCarrito(${idCoche})"]`).closest('.card');
        const modelo = card.querySelector('.card-title').textContent;
        const precio = parseFloat(card.querySelector('.card-text').textContent.replace('Precio: ', '').replace(' €', ''));
        const imagen = card.querySelector('.card-img-top').src.split('/').pop();
        
        // Verificar si ya está en el carrito
        const itemExistente = carrito.find(item => item.id === idCoche);
        
        if (itemExistente) {
            itemExistente.cantidad += 1;
        } else {
            carrito.push({
                id: idCoche,
                modelo: modelo,
                precio: precio,
                imagen: imagen,
                cantidad: 1
            });
        }
        
        localStorage.setItem('carrito', JSON.stringify(carrito));
        actualizarContadorCarrito();
        mostrarToast('Coche añadido al carrito');
    };
    
    // Función para eliminar del carrito
    window.eliminarDelCarrito = function(idCoche) {
        carrito = carrito.filter(item => item.id !== idCoche);
        localStorage.setItem('carrito', JSON.stringify(carrito));
        actualizarContadorCarrito();
        actualizarModalCarrito();
        mostrarToast('Coche eliminado del carrito');
    };
    
    // Actualizar contador del carrito
    function actualizarContadorCarrito() {
        const totalItems = carrito.reduce((total, item) => total + item.cantidad, 0);
        document.getElementById('carritoCantidad').textContent = totalItems;
    }
    
    // Actualizar modal del carrito
    function actualizarModalCarrito() {
        const modalCarrito = document.getElementById('modalCarrito');
        modalCarrito.innerHTML = '';
        
        if (carrito.length === 0) {
            modalCarrito.innerHTML = '<li class="list-group-item">El carrito está vacío</li>';
            return;
        }
        
        let total = 0;
        
        carrito.forEach(item => {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.innerHTML = `
                <div class="d-flex align-items-center">
                    <img src="img/${item.imagen}" alt="${item.modelo}" style="width: 80px; height: auto; margin-right: 15px; object-fit: cover;">
                    <div>
                        <h6>${item.modelo}</h6>
                        <small>${item.precio.toFixed(2)} € x ${item.cantidad}</small>
                    </div>
                </div>
                <div>
                    <span class="badge bg-primary rounded-pill me-3">${(item.precio * item.cantidad).toFixed(2)} €</span>
                    <button class="btn btn-danger btn-sm" onclick="eliminarDelCarrito(${item.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            modalCarrito.appendChild(li);
            total += item.precio * item.cantidad;
        });
        
        // Añadir total
        const liTotal = document.createElement('li');
        liTotal.className = 'list-group-item d-flex justify-content-between fw-bold bg-light';
        liTotal.innerHTML = `
            <span>Total:</span>
            <span>${total.toFixed(2)} €</span>
        `;
        modalCarrito.appendChild(liTotal);
    }
    
    // Finalizar compra
    window.finalizarCompra = function() {
        if (carrito.length === 0) {
            mostrarToast('El carrito está vacío', 'danger');
            return;
        }
        
        fetch('procesar_compra.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ carrito: carrito })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarToast('Compra realizada con éxito', 'success');
                carrito = [];
                localStorage.removeItem('carrito');
                actualizarContadorCarrito();
                actualizarModalCarrito();
                // Cierra el modal usando Bootstrap JavaScript
                bootstrap.Modal.getInstance(document.getElementById('carritoModal')).hide();
            } else {
                mostrarToast(data.message || 'Error al procesar la compra', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarToast('Error al conectar con el servidor', 'danger');
        });
    };
    
    // Mostrar notificación toast
    function mostrarToast(mensaje, tipo = 'success') {
        const toastContainer = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast show align-items-center text-white bg-${tipo} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${mensaje}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        toastContainer.appendChild(toast);
        
        // Eliminar el toast después de 3 segundos
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
    
    // Actualizar modal cuando se abre
    document.getElementById('carritoModal').addEventListener('show.bs.modal', function() {
        actualizarModalCarrito();
    });
});