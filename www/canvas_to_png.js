// canvas_to_png.js
// Convierte JSON de Fabric.js a imagen PNG
// Uso: node canvas_to_png.js <json_string> <output_file.png>
// Subir a: /var/node_canvas/canvas_to_png.js

const { createCanvas, loadImage } = require('canvas');
const fabric = require('fabric').fabric;
const fs = require('fs');

// Leer argumentos
const jsonInput  = process.argv[2]; // JSON de Fabric.js
const outputFile = process.argv[3]; // ruta del PNG a guardar

if (!jsonInput || !outputFile) {
    process.stderr.write("Uso: node canvas_to_png.js '<json>' <output.png>\n");
    process.exit(1);
}

let canvasJson;
try {
    canvasJson = JSON.parse(jsonInput);
} catch (e) {
    process.stderr.write("Error parseando JSON: " + e.message + "\n");
    process.exit(1);
}

// Dimensiones del canvas (mismo que el frontend: 450x650)
const WIDTH  = 450;
const HEIGHT = 650;

// Crear canvas con node-canvas
const nodeCanvas = createCanvas(WIDTH, HEIGHT);
const fabricCanvas = new fabric.StaticCanvas(null, {
    width:  WIDTH,
    height: HEIGHT
});

fabricCanvas.loadFromJSON(canvasJson, function() {
    fabricCanvas.renderAll();

    // Obtener imagen como PNG base64
    const dataURL = fabricCanvas.toDataURL({ format: 'png', multiplier: 1 });
    const base64  = dataURL.replace(/^data:image\/png;base64,/, '');
    const buffer  = Buffer.from(base64, 'base64');

    fs.writeFileSync(outputFile, buffer);
    process.stdout.write("OK:" + outputFile + "\n");
    process.exit(0);
});