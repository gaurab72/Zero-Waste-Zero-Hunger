/**
 * public/assets/js/hero-3d.js
 * Renders a 3D Particle Globe representing global food connection
 */

import * as THREE from 'https://cdn.skypack.dev/three@0.136.0';

const container = document.getElementById('canvas-container');

if (container) {
    const scene = new THREE.Scene();
    
    // Camera Setup
    const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
    camera.position.z = 15;

    // Renderer Setup
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    container.appendChild(renderer.domElement);

    // Particle Globe
    const particlesGeometry = new THREE.BufferGeometry();
    const particleCount = 1200;
    
    const posArray = new Float32Array(particleCount * 3);
    
    for(let i = 0; i < particleCount * 3; i++) {
        // Distributed randomly in a sphere
        // using spherical coordinates
        const phi = Math.acos( -1 + ( 2 * i ) / particleCount );
        const theta = Math.sqrt( particleCount * Math.PI ) * phi;
        
        const r = 8; // Radius
        
        posArray[i*3] = r * Math.cos(theta) * Math.sin(phi);     // x
        posArray[i*3+1] = r * Math.sin(theta) * Math.sin(phi);   // y
        posArray[i*3+2] = r * Math.cos(phi);                     // z
        
        // Add some jitter
        posArray[i*3] += (Math.random() - 0.5) * 0.5;
        posArray[i*3+1] += (Math.random() - 0.5) * 0.5;
        posArray[i*3+2] += (Math.random() - 0.5) * 0.5;
    }
    
    particlesGeometry.setAttribute('position', new THREE.BufferAttribute(posArray, 3));
    
    // Neon Green Material
    const material = new THREE.PointsMaterial({
        size: 0.12,
        color: 0x00ff88,
        transparent: true,
        opacity: 0.8,
        blending: THREE.AdditiveBlending
    });
    
    const particlesMesh = new THREE.Points(particlesGeometry, material);
    scene.add(particlesMesh);

    // Connecting Lines (Outer Shell)
    const wireframe = new THREE.WireframeGeometry(new THREE.IcosahedronGeometry(8.2, 1));
    const lineMaterial = new THREE.LineBasicMaterial({ color: 0x00ccff, opacity: 0.1, transparent: true });
    const lineSphere = new THREE.LineSegments(wireframe, lineMaterial);
    scene.add(lineSphere);

    // Mouse Interaction
    let mouseX = 0;
    let mouseY = 0;
    
    document.addEventListener('mousemove', (event) => {
        mouseX = event.clientX / window.innerWidth - 0.5;
        mouseY = event.clientY / window.innerHeight - 0.5;
    });

    // Animation Loop
    const clock = new THREE.Clock();
    
    function animate() {
        requestAnimationFrame(animate);
        const elapsedTime = clock.getElapsedTime();

        // Rotation
        particlesMesh.rotation.y = elapsedTime * 0.05;
        lineSphere.rotation.y = elapsedTime * 0.05;
        
        // Subtle mouse parallax
        particlesMesh.rotation.x += mouseY * 0.001;
        particlesMesh.rotation.y += mouseX * 0.001;

        renderer.render(scene, camera);
    }
    
    animate();

    // Handle Resize
    window.addEventListener('resize', () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });
}
