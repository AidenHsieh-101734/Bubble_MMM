/**
 * Surreal Background Animation using Three.js
 * Creates floating glass bubbles with mouse interaction.
 */

export function initSurrealBackground(containerId) {
    const container = document.getElementById(containerId);
    if (!container) {
        console.error(`Container #${containerId} not found.`);
        return;
    }

    // --- Scene Setup ---
    const scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2(0x050505, 0.002);

    const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
    camera.position.z = 20;

    const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    container.appendChild(renderer.domElement);

    // --- Objects: Glass Bubbles ---
    const geometry = new THREE.SphereGeometry(1, 32, 32);
    const material = new THREE.MeshPhysicalMaterial({
        color: 0x8b5cf6, // Violet base
        roughness: 0,
        metalness: 0.1,
        transmission: 0.9,
        transparent: true,
        opacity: 0.3,
        clearcoat: 1.0,
        clearcoatRoughness: 0.1
    });

    const bubbles = [];
    const bubbleCount = 78; // Increased count for "surreal" density

    for (let i = 0; i < bubbleCount; i++) {
        const bubble = new THREE.Mesh(geometry, material);

        // Random positions
        bubble.position.x = (Math.random() - 0.5) * 60;
        bubble.position.y = (Math.random() - 0.5) * 60;
        bubble.position.z = (Math.random() - 0.5) * 40 - 10;

        // Random scales
        const scale = Math.random() * 2.5 + 0.5;
        bubble.scale.set(scale, scale, scale);

        // Custom animation data
        bubble.userData = {
            speedY: Math.random() * 0.03 + 0.005,
            speedX: (Math.random() - 0.5) * 0.02,
            wobbleSpeed: Math.random() * 0.02,
            wobbleOffset: Math.random() * Math.PI * 2,
            initialX: bubble.position.x
        };

        scene.add(bubble);
        bubbles.push(bubble);
    }

    // --- Lighting ---
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.4);
    scene.add(ambientLight);

    const pointLight1 = new THREE.PointLight(0xc084fc, 2, 50);
    pointLight1.position.set(10, 10, 10);
    scene.add(pointLight1);

    const pointLight2 = new THREE.PointLight(0xe879f9, 2, 50);
    pointLight2.position.set(-10, -10, 10);
    scene.add(pointLight2);

    // --- Interaction ---
    let mouseX = 0;
    let mouseY = 0;
    let targetX = 0;
    let targetY = 0;

    // Use window for mouse move to catch it everywhere
    window.addEventListener('mousemove', (event) => {
        mouseX = (event.clientX - window.innerWidth / 2) * 0.01;
        mouseY = (event.clientY - window.innerHeight / 2) * 0.01;
    });

    // --- Animation Loop ---
    function animate() {
        requestAnimationFrame(animate);

        const time = Date.now() * 0.001;

        // Smooth camera movement
        targetX = mouseX * 0.5;
        targetY = mouseY * 0.5;

        camera.position.x += (targetX - camera.position.x) * 0.05;
        camera.position.y += (-targetY - camera.position.y) * 0.05;
        camera.lookAt(scene.position);

        // Animate Bubbles
        bubbles.forEach(bubble => {
            // Rise
            bubble.position.y += bubble.userData.speedY;
            bubble.position.x += bubble.userData.speedX;

            // Reset loop
            if (bubble.position.y > 30) {
                bubble.position.y = -30;
                bubble.position.x = bubble.userData.initialX + (Math.random() - 0.5) * 10;
            }

            // Wobble
            const wobble = Math.sin(time + bubble.userData.wobbleOffset) * 0.05;
            // bubble.scale.setScalar(bubble.scale.x + wobble * 0.001); // Minimal breathe

            // Rotate
            bubble.rotation.x += 0.002;
            bubble.rotation.y += 0.002;
        });

        renderer.render(scene, camera);
    }

    animate();

    // --- Resize Handler ---
    window.addEventListener('resize', () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });
}
