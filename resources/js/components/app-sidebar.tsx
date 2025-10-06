import { Link } from '@inertiajs/react'
import { Divider, NavLink, Stack, Text, Group, ThemeIcon } from '@mantine/core'
import { Calendar, Home, Building2, Settings, Activity, Users } from 'lucide-react'
import React from 'react'

type Props = {
    active: string
}

const AppSidebar = ({ active }: Props) => {
  const navItems = [
    { key: "home", label: "Tableau de bord", href: "/", icon: Home },
    { key: "salles", label: "Salles", href: "/salles", icon: Building2 },
    { key: "cours", label: "Plannings", href: "/cours", icon: Calendar },
    { key: "enseignants", label: "Enseignants", href: "/enseignants", icon: Users },
    { key: "logs", label: "Logs système", href: "/logs", icon: Activity },
  ]

  return (
    <Stack mt={24} pb={24} align='start' justify='space-between' h={"100%"}>
        <Stack gap={4} w={"100%"}>
            <Text size="xs" fw={600} c="dimmed" mb="sm" px="md" tt="uppercase">
              Navigation
            </Text>
            {navItems.map((item) => (
              <NavLink 
                key={item.key}
                href={item.href}
                component={Link}
                label={item.label} 
                active={active === item.key} 
                leftSection={
                  <ThemeIcon 
                    size="sm" 
                    variant={active === item.key ? "filled" : "light"}
                    color={active === item.key ? "deepBlue" : "gray"}
                  >
                    <item.icon size={16} />
                  </ThemeIcon>
                }
                fw={500} 
                variant='subtle'
                className="rounded-lg mx-2"
                styles={{
                  root: {
                    borderRadius: '8px',
                    margin: '0 8px',
                    padding: '12px 16px',
                  },
                  label: {
                    fontSize: '14px',
                  }
                }}
              />
            ))}
        </Stack>
        
        <Stack w={"100%"} gap="sm">
            <Divider w={"90%"} mx="auto" color="gray.3" />
            <Text size="xs" fw={600} c="dimmed" px="md" tt="uppercase">
              Administration
            </Text>
            <NavLink 
              label="Paramètres" 
              fw={500} 
              leftSection={
                <ThemeIcon size="sm" variant="light" color="gray">
                  <Settings size={16} />
                </ThemeIcon>
              }
              variant='subtle'
              className="rounded-lg mx-2"
              styles={{
                root: {
                  borderRadius: '8px',
                  margin: '0 8px',
                  padding: '12px 16px',
                },
                label: {
                  fontSize: '14px',
                }
              }}
            />
        </Stack>
    </Stack>
  )
}

export default AppSidebar