#!/bin/bash

# Test Docker build script
echo "🐳 Testing Docker build..."

# Test the main Dockerfile
echo "📦 Building main Dockerfile..."
docker build -t fintrack-test:latest . 2>&1 | tee build.log

if [ $? -eq 0 ]; then
    echo "✅ Main Dockerfile build successful!"
else
    echo "❌ Main Dockerfile build failed!"
    echo "Last 20 lines of build log:"
    tail -20 build.log
    exit 1
fi

# Test the Render Dockerfile
echo "📦 Building Render Dockerfile..."
docker build -f Dockerfile.render -t fintrack-render-test:latest . 2>&1 | tee build-render.log

if [ $? -eq 0 ]; then
    echo "✅ Render Dockerfile build successful!"
else
    echo "❌ Render Dockerfile build failed!"
    echo "Last 20 lines of build log:"
    tail -20 build-render.log
    exit 1
fi

echo "🎉 All Docker builds completed successfully!"

# Cleanup test images
docker rmi fintrack-test:latest fintrack-render-test:latest 2>/dev/null

echo "🧹 Cleanup completed!"