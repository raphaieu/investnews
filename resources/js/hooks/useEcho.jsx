import { useEffect, useState } from 'react'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

if (typeof window !== 'undefined') {
    window.Pusher = Pusher
}

let echoInstance = null

export function useEcho() {
    const [echo, setEcho] = useState(null)
    const [isConnected, setIsConnected] = useState(false)

    useEffect(() => {
        let instance = echoInstance

        if (!instance && typeof window !== 'undefined') {
            const appKey = import.meta.env.VITE_REVERB_APP_KEY
            if (!appKey) return

            const scheme = import.meta.env.VITE_REVERB_SCHEME || 'http'

            instance = new Echo({
                broadcaster: 'reverb',
                key: appKey,
                wsHost: import.meta.env.VITE_REVERB_HOST || 'localhost',
                wsPort: parseInt(import.meta.env.VITE_REVERB_PORT) || 8080,
                wssPort: parseInt(import.meta.env.VITE_REVERB_PORT) || 8080,
                forceTLS: scheme === 'https',
                enabledTransports: ['ws', 'wss'],
                disableStats: true,
            })

            echoInstance = instance
        }

        if (!instance) return

        setEcho(instance)

        const pusherConn = instance.connector.pusher.connection

        const onConnected = () => setIsConnected(true)
        const onDisconnected = () => setIsConnected(false)

        pusherConn.bind('connected', onConnected)
        pusherConn.bind('disconnected', onDisconnected)

        if (pusherConn.state === 'connected') {
            setIsConnected(true)
        }

        return () => {
            pusherConn.unbind('connected', onConnected)
            pusherConn.unbind('disconnected', onDisconnected)
        }
    }, [])

    return { echo, isConnected }
}

export function usePublicChannel(channelName) {
    const { echo, isConnected } = useEcho()
    const [channel, setChannel] = useState(null)

    useEffect(() => {
        if (!echo || !isConnected) return

        const ch = echo.channel(channelName)
        setChannel(ch)

        return () => {
            echo.leave(channelName)
            setChannel(null)
        }
    }, [echo, isConnected, channelName])

    return { channel, isConnected }
}
