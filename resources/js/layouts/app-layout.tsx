import AppHeader from '@/components/app-header'
import AppSidebar from '@/components/app-sidebar'
import { AppShell } from '@mantine/core'
import React, { PropsWithChildren } from 'react'

const AppLayout = ({ children, active }: PropsWithChildren<{ active: string }>) => {
  return (
    <AppShell
        header={{ height: 80  }}
        padding={"md"}
        navbar={{
            breakpoint: "sm",
            width: 240,
        }}
    >
        <AppShell.Header bg={"deepBlue.9"} c={"white"} px={"xl"}>
            <AppHeader />
        </AppShell.Header>
        <AppShell.Navbar>
            <AppSidebar active={active} />
        </AppShell.Navbar>
        <AppShell.Main>
            {children}
        </AppShell.Main>
    </AppShell>
  )
}

export default AppLayout