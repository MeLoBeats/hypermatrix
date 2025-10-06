import { ActionIcon, Menu } from '@mantine/core'
import { CircleUserIcon } from 'lucide-react'
import React from 'react'

const AccountMenu = () => {
  return (
    <Menu shadow='md' width={200}>
        <Menu.Target>
            <ActionIcon size={"xl"}>
                <CircleUserIcon />
            </ActionIcon>
        </Menu.Target>
        <Menu.Dropdown>
            <Menu.Label>Gestion profil</Menu.Label>
            <Menu.Item c={"red"}>Déconnexion</Menu.Item>
        </Menu.Dropdown>
    </Menu>
  )
}

export default AccountMenu