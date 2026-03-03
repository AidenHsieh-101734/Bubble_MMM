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
    camera.position.z = 25;

    const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    container.appendChild(renderer.domElement);

    // --- Objects: Glass Bubbles ---
    const geometry = new THREE.SphereGeometry(1, 32, 32);
    // Enhanced material for a more "surreal" glass look
    const material = new THREE.MeshPhysicalMaterial({
        color: 0xffffff,
        emissive: 0x9055f8,
        emissiveIntensity: 0.1,
        roughness: 0,
        metalness: 0.2,
        transmission: 1,
        transparent: true,
        opacity: 0.4,
        clearcoat: 1.0,
        clearcoatRoughness: 0.1,
        ior: 1.5,
        reflectivity: 0.5
    });

    const bubbles = [];
    const bubbleCount = 100;

    for (let i = 0; i < bubbleCount; i++) {
        const bubble = new THREE.Mesh(geometry, material.clone());

        // Vary colors slightly for surreal effect
        const hue = Math.random() * 0.1 + 0.75; // Purple/Pink range
        bubble.material.color.setHSL(hue, 0.5, 0.9);
        bubble.material.emissive.setHSL(hue, 0.8, 0.5);

        // Random positions spread wider
        bubble.position.x = (Math.random() - 0.5) * 80;
        bubble.position.y = (Math.random() - 0.5) * 80;
        bubble.position.z = (Math.random() - 0.5) * 50 - 10;

        // Varied scales
        const scale = Math.random() * 3 + 0.5;
        bubble.scale.set(scale, scale, scale);

        // Custom animation data
        bubble.userData = {
            speedY: Math.random() * 0.04 + 0.01,
            speedX: (Math.random() - 0.5) * 0.02,
            wobbleSpeed: Math.random() * 2 + 0.5,
            wobbleOffset: Math.random() * Math.PI * 2,
            initialX: bubble.position.x,
            baseScale: scale
        };

        scene.add(bubble);
        bubbles.push(bubble);
    }

    // --- Lighting ---
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.3);
    scene.add(ambientLight);

    const pointLight1 = new THREE.PointLight(0x9055f8, 3, 100);
    pointLight1.position.set(20, 20, 20);
    scene.add(pointLight1);

    const pointLight2 = new THREE.PointLight(0xEEAECA, 3, 100);
    pointLight2.position.set(-20, -20, 20);
    scene.add(pointLight2);

    // Moving light for dynamic reflections
    const movingLight = new THREE.PointLight(0x00ffff, 2, 80);
    scene.add(movingLight);

    // --- Interaction ---
    let mouseX = 0;
    let mouseY = 0;
    let targetX = 0;
    let targetY = 0;

    window.addEventListener('mousemove', (event) => {
        mouseX = (event.clientX - window.innerWidth / 2) * 0.05;
        mouseY = (event.clientY - window.innerHeight / 2) * 0.05;
    });

    // --- Animation Loop ---
    function animate() {
        requestAnimationFrame(animate);

        const time = Date.now() * 0.001;

        // Animate moving light
        movingLight.position.x = Math.sin(time * 0.5) * 30;
        movingLight.position.y = Math.cos(time * 0.3) * 30;
        movingLight.position.z = Math.sin(time * 0.2) * 20 + 10;

        // Smooth camera movement with damping
        targetX = mouseX * 0.5;
        targetY = mouseY * 0.5;

        camera.position.x += (targetX - camera.position.x) * 0.02;
        camera.position.y += (-targetY - camera.position.y) * 0.02;
        camera.lookAt(scene.position);

        // Animate Bubbles
        bubbles.forEach((bubble) => {
            const ud = bubble.userData;

            // Interaction: repulsive force from mouse/camera look
            const dx = camera.position.x - bubble.position.x;
            const dy = camera.position.y - bubble.position.y;
            const dist = Math.sqrt(dx * dx + dy * dy);

            if (dist < 15) {
                ud.speedX += -dx * 0.001;
                ud.speedY += -dy * 0.001;
            }

            // Damping logic for interaction
            ud.speedX *= 0.98;
            // Maintain base upward drift
            if (ud.speedY < 0.01) ud.speedY += 0.0005;

            // Rise
            bubble.position.y += ud.speedY;
            bubble.position.x += ud.speedX;

            // Reset loop
            if (bubble.position.y > 40) {
                bubble.position.y = -40;
                bubble.position.x = ud.initialX + (Math.random() - 0.5) * 20;
                ud.speedX = (Math.random() - 0.5) * 0.02;
                ud.speedY = Math.random() * 0.04 + 0.01;
            }

            // Wobble/Pulse
            const wobble = Math.sin(time * ud.wobbleSpeed + ud.wobbleOffset) * 0.1;
            bubble.scale.setScalar(ud.baseScale + wobble);

            // Rotate
            bubble.rotation.x += 0.001;
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
